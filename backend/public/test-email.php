<?php
/**
 * Simple email test endpoint
 * Access via: https://admin.tfcmockup.com/test-email.php?email=your@email.com
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

// Get email from query parameter
$recipientEmail = $_GET['email'] ?? null;

if (!$recipientEmail) {
    echo json_encode([
        'success' => false,
        'message' => 'Please provide email parameter. Example: ?email=your@email.com'
    ], JSON_PRETTY_PRINT);
    exit;
}

if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid email address'
    ], JSON_PRETTY_PRINT);
    exit;
}

try {
    // Get mail configuration
    $config = [
        'mailer' => config('mail.default'),
        'host' => config('mail.mailers.smtp.host'),
        'port' => config('mail.mailers.smtp.port'),
        'encryption' => config('mail.mailers.smtp.encryption'),
        'username' => config('mail.mailers.smtp.username'),
        'from_address' => config('mail.from.address'),
        'from_name' => config('mail.from.name'),
    ];

    // Send test email
    Illuminate\Support\Facades\Mail::raw(
        'This is a test email from Kuantan188 Booking System. If you receive this, your Brevo email configuration is working correctly!',
        function ($message) use ($recipientEmail) {
            $message->to($recipientEmail)
                ->subject('Test Email - Kuantan188 Booking System');
        }
    );

    echo json_encode([
        'success' => true,
        'message' => 'Test email sent successfully!',
        'recipient' => $recipientEmail,
        'timestamp' => date('Y-m-d H:i:s'),
        'configuration' => [
            'mailer' => $config['mailer'],
            'host' => $config['host'],
            'port' => $config['port'],
            'from' => $config['from_address'],
        ]
    ], JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email',
        'error' => $e->getMessage(),
        'configuration' => [
            'mailer' => $config['mailer'] ?? 'unknown',
            'host' => $config['host'] ?? 'unknown',
            'port' => $config['port'] ?? 'unknown',
            'from' => $config['from_address'] ?? 'unknown',
        ]
    ], JSON_PRETTY_PRINT);
}
