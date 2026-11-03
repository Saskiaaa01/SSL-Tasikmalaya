<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?string $token;
    protected ?string $phoneNumberId;
    protected string $apiVersion;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->apiVersion = config('services.whatsapp.api_version', 'v21.0');
    }

    /**
     * Send a WhatsApp message.
     *
     * @param string $recipient Phone number with country code (e.g. 628123456789)
     * @param string $message Text message content
     * @return array
     */
    public function sendMessage(string $recipient, string $message): array
    {
        // Sanitize recipient phone number
        $recipient = preg_replace('/[^0-9]/', '', $recipient);

        if (empty($this->token) || empty($this->phoneNumberId)) {
            Log::warning("WhatsApp Cloud API credentials not configured. Message simulated for {$recipient}: {$message}");
            return [
                'status' => 'simulated',
                'message' => 'WhatsApp credentials not configured in .env. Notification logged only.',
                'recipient' => $recipient,
            ];
        }

        try {
            $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

            $response = Http::withToken($this->token)
                ->timeout(10)
                ->post($url, [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $recipient,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $message,
                    ],
                ]);

            if ($response->successful()) {
                Log::info("WhatsApp message sent successfully to {$recipient}");
                return [
                    'status' => 'success',
                    'response' => $response->json(),
                ];
            }

            Log::error("WhatsApp Cloud API error: " . $response->body());
            return [
                'status' => 'error',
                'response' => $response->json(),
            ];
        } catch (\Exception $e) {
            Log::error("Failed to send WhatsApp message: " . $e->getMessage());
            return [
                'status' => 'exception',
                'error' => $e->getMessage(),
            ];
        }
    }
}
