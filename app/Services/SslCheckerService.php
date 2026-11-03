<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service to perform SSL/TLS certificate checks with SSRF protection.
 *
 * Responsibilities:
 *   - Resolve hostname to IP addresses (IPv4 & IPv6) and ensure none belong to private/non‑public ranges.
 *   - Open a TLS connection with a configurable timeout (default 10 seconds).
 *   - Extract certificate details: valid from, valid to, subject CN, issuer CN, full chain, SHA‑256 fingerprint.
 *   - Return a structured array or throw an exception on failure.
 *
 * The service is deliberately lightweight and does not depend on any external packages beyond PHP core.
 */
class SslCheckerService
{
    /**
     * Maximum connection timeout in seconds.
     */
    protected int $timeout;

    public function __construct(int $timeout = 10)
    {
        $this->timeout = $timeout;
    }

    /**
     * Perform the SSL check for a given host (and optional port, default 443).
     *
     * @param string $host
     * @param int $port
     * @return array
     * @throws Exception when verification fails or SSRF is detected.
     */
    public function check(string $host, int $port = 443): array
    {
        // Normalize host (remove scheme if accidentally included)
        $host = preg_replace('#^https?://#i', '', $host);
        $host = rtrim($host, '/');

        // Resolve DNS – we need all IPs to verify none are private.
        $ips = $this->resolveHost($host);
        $this->assertPublicIps($ips);

        // Build the context for a TLS stream with timeout.
        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'capture_peer_cert_chain' => true,
                'verify_peer' => true,
                'verify_peer_name' => true,
                // Enable SNI with the host name.
                'SNI_enabled' => true,
                'SNI_server_name' => $host,
                // Timeout is controlled by stream_set_timeout after opening.
            ],
        ]);

        $remote = "ssl://{$host}:{$port}";
        $client = @stream_socket_client(
            $remote,
            $errno,
            $errstr,
            $this->timeout,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if ($client === false) {
            throw new Exception("Unable to connect to {$host}:{$port} – {$errstr} ({$errno})");
        }

        // Apply read timeout – this also guards against very slow handshakes.
        stream_set_timeout($client, $this->timeout);

        $params = stream_context_get_params($client);
        // Extract the primary certificate and the chain.
        $certResource = $params['options']['ssl']['peer_certificate'] ?? null;
        $chainResources = $params['options']['ssl']['peer_certificate_chain'] ?? [];

        if (!$certResource) {
            fclose($client);
            throw new Exception('Failed to retrieve peer certificate');
        }

        // Parse the primary cert.
        $certInfo = openssl_x509_parse($certResource);
        if ($certInfo === false) {
            fclose($client);
            throw new Exception('Failed to parse peer certificate');
        }

        // Compute SHA‑256 fingerprint.
        $pem = $this->exportPem($certResource);
        $fingerprint = hash('sha256', $pem);

        // Build chain PEMs for reference.
        $chainPem = [];
        foreach ($chainResources as $chainCert) {
            $chainPem[] = $this->exportPem($chainCert);
        }

        fclose($client);

        return [
            'host' => $host,
            'port' => $port,
            'valid_from' => $this->convertTimestamp($certInfo['validFrom_time_t'] ?? null),
            'valid_to'   => $this->convertTimestamp($certInfo['validTo_time_t'] ?? null),
            'subject_cn' => $certInfo['subject']['CN'] ?? null,
            'issuer_cn'  => $certInfo['issuer']['CN'] ?? null,
            'fingerprint_sha256' => $fingerprint,
            'chain_pem' => $chainPem,
            'days_remaining' => $this->calculateDaysRemaining($certInfo['validTo_time_t'] ?? null),
        ];
    }

    /**
     * Resolve a hostname to an array of IP strings.
     */
    protected function resolveHost(string $host): array
    {
        $records = dns_get_record($host, DNS_A + DNS_AAAA);
        $ips = [];
        foreach ($records as $record) {
            if (isset($record['ip'])) {
                $ips[] = $record['ip'];
            } elseif (isset($record['ipv6'])) {
                $ips[] = $record['ipv6'];
            }
        }
        if (empty($ips)) {
            throw new Exception("DNS resolution failed for host {$host}");
        }
        return $ips;
    }

    /**
     * Ensure none of the given IPs belong to private or non‑public ranges.
     * Throws Exception if a prohibited IP is found.
     */
    protected function assertPublicIps(array $ips): void
    {
        foreach ($ips as $ip) {
            if ($this->isPrivateIp($ip)) {
                throw new Exception("SSRF protection: resolved IP {$ip} is non‑public");
            }
        }
    }

    /**
     * Detect private / loopback / link‑local / reserved IP ranges.
     */
    protected function isPrivateIp(string $ip): bool
    {
        // IPv4 checks.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $long = ip2long($ip);
            // 10.0.0.0/8
            if (($long & ip2long('255.0.0.0')) === ip2long('10.0.0.0')) return true;
            // 172.16.0.0/12
            if (($long & ip2long('255.240.0.0')) === ip2long('172.16.0.0')) return true;
            // 192.168.0.0/16
            if (($long & ip2long('255.255.0.0')) === ip2long('192.168.0.0')) return true;
            // 127.0.0.0/8 (loopback)
            if (($long & ip2long('255.0.0.0')) === ip2long('127.0.0.0')) return true;
            // 169.254.0.0/16 (link‑local)
            if (($long & ip2long('255.255.0.0')) === ip2long('169.254.0.0')) return true;
        }

        // IPv6 checks.
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Loopback ::1
            if ($ip === '::1') return true;
            // Unique local addresses fc00::/7
            if (Str::startsWith(Str::lower($ip), 'fc') || Str::startsWith(Str::lower($ip), 'fd')) return true;
            // Link‑local fe80::/10
            if (Str::startsWith(Str::lower($ip), 'fe80')) return true;
        }
        return false;
    }

    /**
     * Convert a Unix timestamp to ISO‑8601 string (UTC).
     */
    protected function convertTimestamp(?int $timestamp): ?string
    {
        if ($timestamp === null) return null;
        return gmdate('c', $timestamp);
    }

    /**
     * Export a certificate resource to PEM string.
     */
    protected function exportPem($certResource): string
    {
        $pem = '';
        openssl_x509_export($certResource, $pem);
        return $pem;
    }

    /**
     * Calculate days remaining until expiration.
     */
    protected function calculateDaysRemaining(?int $validToTimestamp): ?int
    {
        if ($validToTimestamp === null) return null;
        $now = time();
        $diff = $validToTimestamp - $now;
        return (int) floor($diff / 86400);
    }
}
?>
