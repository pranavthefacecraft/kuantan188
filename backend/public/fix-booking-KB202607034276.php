<?php
/**
 * Fix booking KB202607034276 ticket counts
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: application/json');

$bookingRef = 'KB202607034276';

try {
    $booking = \App\Models\Booking::where('booking_reference', $bookingRef)->first();
    
    if (!$booking) {
        echo json_encode([
            'status' => 'error',
            'message' => "Booking not found: {$bookingRef}",
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    // Store original values
    $originalData = [
        'quantity' => $booking->quantity,
        'adult_tickets' => $booking->adult_tickets,
        'child_tickets' => $booking->child_tickets,
    ];
    
    // Update to reflect actual quantity
    // Since quantity is 4 and these were likely teenager/university tickets
    // Set adult_tickets to match the total quantity
    $booking->adult_tickets = $booking->quantity; // 4
    $booking->child_tickets = 0;
    $booking->save();
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Booking ticket counts updated successfully',
        'booking_reference' => $bookingRef,
        'original' => $originalData,
        'updated' => [
            'quantity' => $booking->quantity,
            'adult_tickets' => $booking->adult_tickets,
            'child_tickets' => $booking->child_tickets,
        ],
        'note' => 'Adult tickets now includes all non-child tickets (teenagers, university students, adults)',
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => explode("\n", $e->getTraceAsString()),
    ], JSON_PRETTY_PRINT);
}
