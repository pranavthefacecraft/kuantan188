<?php
/**
 * Check specific booking details
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: application/json');

$bookingRef = $_GET['ref'] ?? 'KB202607034276';

try {
    $booking = \App\Models\Booking::where('booking_reference', $bookingRef)->first();
    
    if (!$booking) {
        echo json_encode([
            'status' => 'error',
            'message' => "Booking not found: {$bookingRef}",
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    echo json_encode([
        'status' => 'success',
        'booking_reference' => $booking->booking_reference,
        'customer_name' => $booking->customer_name,
        'customer_email' => $booking->email ?? $booking->customer_email,
        'event_title' => $booking->event_title,
        'event_date' => $booking->event_date,
        'quantity' => $booking->quantity,
        'adult_tickets' => $booking->adult_tickets,
        'child_tickets' => $booking->child_tickets,
        'adult_price' => $booking->adult_price,
        'child_price' => $booking->child_price,
        'total_amount' => $booking->total_amount,
        'payment_method' => $booking->payment_method,
        'payment_status' => $booking->payment_status,
        'booking_status' => $booking->status ?? $booking->booking_status ?? 'N/A',
        'created_at' => $booking->created_at->format('Y-m-d H:i:s'),
        'calculation' => [
            'adult_total' => $booking->adult_tickets . ' × RM' . number_format($booking->adult_price, 2) . ' = RM' . number_format($booking->adult_tickets * $booking->adult_price, 2),
            'child_total' => $booking->child_tickets . ' × RM' . number_format($booking->child_price, 2) . ' = RM' . number_format($booking->child_tickets * $booking->child_price, 2),
            'expected_total' => 'RM' . number_format(($booking->adult_tickets * $booking->adult_price) + ($booking->child_tickets * $booking->child_price), 2),
            'actual_total' => 'RM' . number_format($booking->total_amount, 2),
        ],
        'all_fields' => $booking->toArray(),
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => explode("\n", $e->getTraceAsString()),
    ], JSON_PRETTY_PRINT);
}
