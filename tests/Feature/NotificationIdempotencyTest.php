<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsappNotificationJob;
use App\Models\Domain;
use App\Models\Notification;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class NotificationIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_is_idempotent_for_same_type_and_fingerprint(): void
    {
        $user = User::factory()->create([
            'phone_number' => '628123456789',
        ]);

        $domain = Domain::create([
            'user_id' => $user->id,
            'hostname' => 'portal.tasikmalayakota.go.id',
            'port' => 443,
            'status' => 'active',
        ]);

        $fingerprint = hash('sha256', 'dummy-cert-content');

        // Mock WhatsAppService so real HTTP call is not made
        $mockService = Mockery::mock(WhatsAppService::class);
        $mockService->shouldReceive('sendMessage')
            ->once() // MUST ONLY BE CALLED ONCE
            ->andReturn([
                'status' => 'simulated',
                'recipient' => '628123456789',
            ]);

        // First dispatch: should send and record notification
        $job1 = new SendWhatsappNotificationJob($domain, 'h7', $fingerprint, 7);
        $job1->handle($mockService);

        $this->assertDatabaseHas('notifications', [
            'domain_id' => $domain->id,
            'type' => 'h7',
            'fingerprint_sha256' => $fingerprint,
        ]);
        $this->assertEquals(1, Notification::count());

        // Second dispatch with same domain, type, fingerprint: MUST skip due to idempotency
        $job2 = new SendWhatsappNotificationJob($domain, 'h7', $fingerprint, 7);
        $job2->handle($mockService);

        // Count must still be 1 (no duplicate sent)
        $this->assertEquals(1, Notification::count());
    }

    public function test_new_notification_allowed_when_certificate_fingerprint_changes(): void
    {
        $user = User::factory()->create([
            'phone_number' => '628123456789',
        ]);

        $domain = Domain::create([
            'user_id' => $user->id,
            'hostname' => 'portal.tasikmalayakota.go.id',
            'port' => 443,
            'status' => 'active',
        ]);

        $fingerprintOld = hash('sha256', 'old-cert');
        $fingerprintRenewed = hash('sha256', 'renewed-cert');

        $mockService = Mockery::mock(WhatsAppService::class);
        $mockService->shouldReceive('sendMessage')
            ->twice() // Called for old and for renewed
            ->andReturn(['status' => 'simulated']);

        $jobOld = new SendWhatsappNotificationJob($domain, 'h30', $fingerprintOld, 30);
        $jobOld->handle($mockService);

        $jobRenewed = new SendWhatsappNotificationJob($domain, 'h30', $fingerprintRenewed, 30);
        $jobRenewed->handle($mockService);

        $this->assertEquals(2, Notification::count());
    }
}

