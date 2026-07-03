<?php
/**
 * Test admin booking notification email
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: application/json');

$email = $_GET['email'] ?? 'admin@kuantan188.com';

try {
    // Find the most recent booking or create a test one
    $booking = \App\Models\Booking::latest()->first();
    
    if (!$booking) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No bookings found in database. Create a test booking first.',
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    $bookingInfo = [
        'booking_reference' => $booking->booking_reference,
        'customer_name' => $booking->customer_name,
        'customer_email' => $booking->email ?? $booking->customer_email,
        'event_title' => $booking->event_title,
        'event_date' => $booking->event_date,
        'quantity' => $booking->quantity,
        'total_amount' => $booking->total_amount,
        'payment_method' => $booking->payment_method,
        'payment_status' => $booking->payment_status,
    ];
    
    // Send the admin notification email
    \Illuminate\Support\Facades\Mail::to($email, 'Admin')
        ->send(new \App\Mail\AdminBookingNotification($booking));
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Admin notification email sent successfully!',
        'recipient' => $email,
        'booking_details' => $bookingInfo,
        'note' => 'This email shows what admins receive when a new booking is created.',
        'configured_admins' => config('services.admin.notification_emails'),
        'timestamp' => now()->format('Y-m-d H:i:s'),
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => explode("\n", $e->getTraceAsString()),
    ], JSON_PRETTY_PRINT);
}
