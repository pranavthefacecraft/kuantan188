<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class BrevoApiTransport extends AbstractTransport
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        
        // Helper to get name or fallback to email
        $getName = fn($addr) => !empty($addr->getName()) ? $addr->getName() : explode('@', $addr->getAddress())[0];
        
        // Build payload for Brevo API
        $payload = [
            'sender' => [
                'email' => $email->getFrom()[0]->getAddress(),
                'name' => $getName($email->getFrom()[0]),
            ],
            'to' => array_map(fn($addr) => [
                'email' => $addr->getAddress(),
                'name' => $getName($addr),
            ], $email->getTo()),
            'subject' => $email->getSubject(),
        ];

        // Handle CC
        if ($email->getCc()) {
            $payload['cc'] = array_map(fn($addr) => [
                'email' => $addr->getAddress(),
                'name' => $getName($addr),
            ], $email->getCc());
        }

        // Handle BCC
        if ($email->getBcc()) {
            $payload['bcc'] = array_map(fn($addr) => [
                'email' => $addr->getAddress(),
                'name' => $getName($addr),
            ], $email->getBcc());
        }

        // Handle reply-to
        if ($email->getReplyTo()) {
            $replyTo = $email->getReplyTo()[0];
            $payload['replyTo'] = [
                'email' => $replyTo->getAddress(),
                'name' => $getName($replyTo),
            ];
        }

        // Get body content
        $htmlBody = $email->getHtmlBody();
        $textBody = $email->getTextBody();

        if ($htmlBody) {
            $payload['htmlContent'] = $htmlBody;
        }
        
        if ($textBody) {
            $payload['textContent'] = $textBody;
        }

        // If no HTML body, use text as HTML
        if (!$htmlBody && $textBody) {
            $payload['htmlContent'] = nl2br(e($textBody));
        }

        // Handle attachments
        $attachments = $email->getAttachments();
        if (!empty($attachments)) {
            $payload['attachment'] = [];
            foreach ($attachments as $attachment) {
                $payload['attachment'][] = [
                    'content' => base64_encode($attachment->getBody()),
                    'name' => $attachment->getFilename() ?? 'attachment',
                ];
            }
        }

        Log::info('Sending email via Brevo API', [
            'to' => $payload['to'],
            'subject' => $payload['subject'],
        ]);

        // Send request to Brevo API
        try {
            $response = Http::withHeaders([
                'api-key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post($this->apiUrl, $payload);

            if (!$response->successful()) {
                $error = $response->json();
                Log::error('Brevo API error', [
                    'status' => $response->status(),
                    'error' => $error,
                ]);
                throw new \Exception(
                    'Brevo API error: ' . ($error['message'] ?? $response->body())
                );
            }

            Log::info('Email sent successfully via Brevo API', [
                'messageId' => $response->json()['messageId'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send email via Brevo API', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function __toString(): string
    {
        return 'brevo_api';
    }
}
