<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BrevoMailService
{
    protected $apiKey;
    protected $fromEmail;
    protected $fromName;

    public function __construct()
    {
        $this->apiKey = env('BREVO_API_KEY');
        // CHANGE THIS to your new domain email
        $this->fromEmail = 'noreply@luckeymm.online';  // ← CHANGE HERE
        $this->fromName = env('MAIL_FROM_NAME', 'MMRatePro Currency');
    }

    public function send($toEmail, $toName, $subject, $htmlContent)
    {
        $response = Http::withHeaders([
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', [
            'sender' => [
                'email' => $this->fromEmail,  // Now uses noreply@luckeymm.online
                'name' => $this->fromName,
            ],
            'to' => [
                [
                    'email' => $toEmail,
                    'name' => $toName,
                ]
            ],
            'subject' => $subject,
            'htmlContent' => $htmlContent,
        ]);

        if ($response->successful()) {
            Log::info('Brevo email sent to: ' . $toEmail);
            return true;
        }

        Log::error('Brevo email failed: ' . $response->body());
        return false;
    }
}
