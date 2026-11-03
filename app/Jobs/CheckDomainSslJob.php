<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Domain;
use App\Models\SslCheck;
use App\Services\SslCheckerService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckDomainSslJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Domain $domain;

    /**
     * Create a new job instance.
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Execute the job.
     */
    public function handle(SslCheckerService $checker): void
    {
        try {
            $result = $checker->check($this->domain->hostname, $this->domain->port ?? 443);

            $days = $result['days_remaining'] ?? 0;
            $status = 'valid';
            if ($days <= 0) {
                $status = 'expired';
            } elseif ($days <= 30) {
                $status = 'expiring';
            }

            $check = SslCheck::create([
                'domain_id' => $this->domain->id,
                'status' => $status,
                'issuer' => $result['issuer_cn'] ?? 'Unknown Issuer',
                'valid_from' => $result['valid_from'] ?? null,
                'valid_to' => $result['valid_to'] ?? null,
                'days_remaining' => $days,
                'fingerprint_sha256' => $result['fingerprint_sha256'] ?? null,
                'chain_details' => $result['chain_pem'] ?? [],
                'error_message' => null,
                'checked_at' => now(),
            ]);

            // Dispatch notification based on days remaining with idempotency
            $fingerprint = $result['fingerprint_sha256'] ?? null;
            if ($status === 'expired') {
                SendWhatsappNotificationJob::dispatch($this->domain, 'expired', $fingerprint, $days);
            } elseif ($days <= 7) {
                SendWhatsappNotificationJob::dispatch($this->domain, 'h7', $fingerprint, $days);
            } elseif ($days <= 14) {
                SendWhatsappNotificationJob::dispatch($this->domain, 'h14', $fingerprint, $days);
            } elseif ($days <= 30) {
                SendWhatsappNotificationJob::dispatch($this->domain, 'h30', $fingerprint, $days);
            }

            AuditLog::create([
                'user_id' => $this->domain->user_id,
                'domain_id' => $this->domain->id,
                'action' => 'ssl_check_success',
                'description' => "SSL check completed for {$this->domain->hostname}: status {$status}, {$days} days remaining.",
                'details' => [
                    'check_id' => $check->id,
                    'status' => $status,
                    'days_remaining' => $days,
                ],
            ]);

        } catch (Exception $e) {
            Log::error("SSL check failed for {$this->domain->hostname}: " . $e->getMessage());

            $check = SslCheck::create([
                'domain_id' => $this->domain->id,
                'status' => 'error',
                'issuer' => null,
                'valid_from' => null,
                'valid_to' => null,
                'days_remaining' => null,
                'fingerprint_sha256' => null,
                'chain_details' => null,
                'error_message' => $e->getMessage(),
                'checked_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $this->domain->user_id,
                'domain_id' => $this->domain->id,
                'action' => 'ssl_check_failed',
                'description' => "SSL check error for {$this->domain->hostname}: {$e->getMessage()}",
                'details' => [
                    'check_id' => $check->id,
                    'error' => $e->getMessage(),
                ],
            ]);
        }
    }
}

