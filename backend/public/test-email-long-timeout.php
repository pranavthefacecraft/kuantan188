<?php
/**
 * Email test with extended timeout and detailed connection logging
 */

// Increase execution time to 5 minutes
set_time_limit(300);
ini_set('max_execution_time', 300);
ini_set('default_socket_timeout', 120);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: application/json');

$email = $_GET['email'] ?? 'pranav@thefacecraft.com';

try {
    echo json_encode([
        'status' => 'connecting',
        'message' => 'Testing SMTP connection with extended timeout...',
        'config' => [
            'host' => config('mail.mailers.smtp.host'),
            'port' => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'username' => config('mail.mailers.smtp.username'),
        ],
        'timeout_settings' => [
            'max_execution_time' => ini_get('max_execution_time'),
            'default_socket_timeout' => ini_get('default_socket_timeout'),
        ]
    ], JSON_PRETTY_PRINT);
    
    flush();
    
    // Try raw socket connection first
    $start = microtime(true);
    $fp = @fsockopen(
        config('mail.mailers.smtp.host'),
        config('mail.mailers.smtp.port'),
        $errno,
        $errstr,
        30
    );
    $socketTime = microtime(true) - $start;
    
    if (!$fp) {
        throw new Exception("Socket connection failed: [$errno] $errstr (took {$socketTime}s)");
    }
    
    fclose($fp);
    
    echo "\n\n" . json_encode([
        'status' => 'socket_success',
        'message' => "Socket connected successfully in {$socketTime}s",
        'next' => 'Attempting Laravel mail send...'
    ], JSON_PRETTY_PRINT);
    
    flush();
    
    // Now try Laravel mail
    $mailStart = microtime(true);
    \Illuminate\Support\Facades\Mail::raw('This is a test email from Kuantan188.', function ($message) use ($email) {
        $message->to($email)
                ->subject('Kuantan188 - Extended Timeout Email Test');
    });
    $mailTime = microtime(true) - $mailStart;
    
    echo "\n\n" . json_encode([
        'status' => 'success',
        'message' => "Email sent successfully!",
        'recipient' => $email,
        'timings' => [
            'socket_connection' => round($socketTime, 2) . 's',
            'mail_send' => round($mailTime, 2) . 's',
            'total' => round($socketTime + $mailTime, 2) . 's'
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo "\n\n" . json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => explode("\n", $e->getTraceAsString())
    ], JSON_PRETTY_PRINT);
}
