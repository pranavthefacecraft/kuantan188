<?php
/**
 * Test Brevo API email sending
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: application/json');

$email = $_GET['email'] ?? 'pranav@thefacecraft.com';

try {
    // Check configuration
    $apiKey = config('services.brevo.api_key');
    $mailer = config('mail.default');
    
    $configInfo = [
        'default_mailer' => $mailer,
        'brevo_api_key_set' => !empty($apiKey),
        'brevo_api_key_length' => $apiKey ? strlen($apiKey) : 0,
        'from_address' => config('mail.from.address'),
        'from_name' => config('mail.from.name'),
    ];
    
    if (empty($apiKey)) {
        throw new \Exception('BREVO_API_KEY is not set in .env file. Please add it and run: php artisan config:clear');
    }
    
    // Test sending
    \Illuminate\Support\Facades\Mail::raw(
        "This is a test email sent via Brevo API.\n\n" .
        "Sent at: " . now()->format('Y-m-d H:i:s') . "\n" .
        "From: Kuantan188 Booking System\n\n" .
        "If you received this email, the Brevo API integration is working correctly!",
        function ($message) use ($email) {
            $message->to($email, 'Test Recipient')
                    ->subject('Kuantan188 - Brevo API Test Email');
        }
    );
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Email sent successfully via Brevo API!',
        'recipient' => $email,
        'config' => $configInfo,
        'timestamp' => now()->format('Y-m-d H:i:s'),
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'config' => $configInfo ?? [],
        'trace' => explode("\n", $e->getTraceAsString()),
    ], JSON_PRETTY_PRINT);
}
