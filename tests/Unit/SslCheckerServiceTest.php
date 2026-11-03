<?php

namespace Tests\Unit;

use App\Services\SslCheckerService;
use Exception;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SslCheckerServiceTest extends TestCase
{
    protected SslCheckerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SslCheckerService(10);
    }

    public function test_ssrf_detection_identifies_private_ipv4_and_ipv6(): void
    {
        $reflector = new ReflectionClass(SslCheckerService::class);
        $method = $reflector->getMethod('isPrivateIp');
        $method->setAccessible(true);

        // Private IPv4
        $this->assertTrue($method->invoke($this->service, '127.0.0.1'));
        $this->assertTrue($method->invoke($this->service, '10.0.0.5'));
        $this->assertTrue($method->invoke($this->service, '172.16.1.10'));
        $this->assertTrue($method->invoke($this->service, '192.168.1.1'));
        $this->assertTrue($method->invoke($this->service, '169.254.1.1'));

        // Public IPv4
        $this->assertFalse($method->invoke($this->service, '8.8.8.8'));
        $this->assertFalse($method->invoke($this->service, '1.1.1.1'));
        $this->assertFalse($method->invoke($this->service, '103.147.222.1'));

        // Private IPv6
        $this->assertTrue($method->invoke($this->service, '::1'));
        $this->assertTrue($method->invoke($this->service, 'fc00::1'));
        $this->assertTrue($method->invoke($this->service, 'fe80::1'));
    }

    public function test_assert_public_ips_throws_exception_on_private_ip(): void
    {
        $reflector = new ReflectionClass(SslCheckerService::class);
        $method = $reflector->getMethod('assertPublicIps');
        $method->setAccessible(true);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SSRF protection');

        $method->invoke($this->service, ['8.8.8.8', '192.168.1.5']);
    }

    public function test_calculate_days_remaining(): void
    {
        $reflector = new ReflectionClass(SslCheckerService::class);
        $method = $reflector->getMethod('calculateDaysRemaining');
        $method->setAccessible(true);

        $now = time();
        $tenDaysLater = $now + (10 * 86400);
        $days = $method->invoke($this->service, $tenDaysLater);

        $this->assertEquals(10, $days);
    }
}

