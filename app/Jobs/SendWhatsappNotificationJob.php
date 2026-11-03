<?php

namespace App\Jobs;

use App\Models\Domain;
use App\Models\Notification;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsappNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Domain $domain;
    public string $type;
    public ?string $fingerprint;
    public ?int $daysRemaining;

    /**
     * Create a new job instance.
     */
    public function __construct(Domain $domain, string $type, ?string $fingerprint = null, ?int $daysRemaining = null)
    {
        $this->domain = $domain;
        $this->type = $type;
        $this->fingerprint = $fingerprint;
        $this->daysRemaining = $daysRemaining;
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsAppService): void
    {
        // 1. Check idempotency: avoid sending duplicate notification for this type and certificate fingerprint
        $existing = Notification::where('domain_id', $this->domain->id)
            ->where('type', $this->type)
            ->where('fingerprint_sha256', $this->fingerprint)
            ->first();

        if ($existing) {
            Log::info("Notification {$this->type} already sent for domain {$this->domain->hostname} with fingerprint {$this->fingerprint}. Skipping.");
            return;
        }

        $recipient = $this->domain->user->phone_number ?? config('services.whatsapp.default_recipient');
        if (empty($recipient)) {
            Log::warning("No phone number available to send WhatsApp notification for domain {$this->domain->hostname}");
            $recipient = 'Simulated / Not Provided';
        }

        $message = $this->composeMessage();

        $result = $whatsAppService->sendMessage($recipient, $message);

        // Record notification in database
        try {
            Notification::create([
                'domain_id' => $this->domain->id,
                'type' => $this->type,
                'channel' => 'whatsapp',
                'recipient' => $recipient,
                'status' => ($result['status'] ?? 'unknown') === 'error' ? 'failed' : 'sent',
                'fingerprint_sha256' => $this->fingerprint,
                'payload' => [
                    'message' => $message,
                    'result' => $result,
                ],
                'sent_at' => now(),
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            Log::info("Notification prevented by DB idempotency constraint for {$this->domain->hostname}");
        }
    }

    protected function composeMessage(): string
    {
        $hostname = $this->domain->hostname;

        if ($this->type === 'expired') {
            return "🚨 [SSLGuard ALERT] Sertifikat SSL untuk domain {$hostname} telah EXPIRED/KADALUARSA! Segera perbarui sertifikat SSL Anda untuk mencegah kendala akses.";
        }

        $days = $this->daysRemaining ?? ($this->type === 'h7' ? 7 : ($this->type === 'h14' ? 14 : 30));
        return "⚠️ [SSLGuard Peringatan] Sertifikat SSL untuk domain {$hostname} akan kadaluarsa dalam {$days} hari lagi ({$this->type}). Silakan lakukan perpanjangan sertifikat segera.";
    }
}

