<?php
/**
 * Fix all bookings where adult_tickets + child_tickets doesn't match quantity
 * This happens when teenager/university tickets were booked but not properly counted
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: application/json');

try {
    // Find all bookings where the ticket counts don't add up
    $bookings = \App\Models\Booking::whereRaw('(adult_tickets + child_tickets) != quantity')
        ->orderBy('created_at', 'desc')
        ->get();
    
    if ($bookings->isEmpty()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'No bookings need fixing. All ticket counts are correct.',
            'fixed_count' => 0,
        ], JSON_PRETTY_PRINT);
        exit;
    }
    
    $fixed = [];
    $errors = [];
    
    foreach ($bookings as $booking) {
        try {
            $original = [
                'booking_reference' => $booking->booking_reference,
                'quantity' => $booking->quantity,
                'adult_tickets' => $booking->adult_tickets,
                'child_tickets' => $booking->child_tickets,
                'sum' => $booking->adult_tickets + $booking->child_tickets,
            ];
            
            // Calculate the difference
            $difference = $booking->quantity - ($booking->adult_tickets + $booking->child_tickets);
            
            // Add the missing tickets to adult_tickets (they were likely teenagers/university)
            $booking->adult_tickets = $booking->adult_tickets + $difference;
            $booking->save();
            
            $fixed[] = [
                'booking_reference' => $booking->booking_reference,
                'original' => $original,
                'updated' => [
                    'adult_tickets' => $booking->adult_tickets,
                    'child_tickets' => $booking->child_tickets,
                    'sum' => $booking->adult_tickets + $booking->child_tickets,
                ],
                'difference_added' => $difference,
            ];
            
        } catch (\Exception $e) {
            $errors[] = [
                'booking_reference' => $booking->booking_reference,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Bulk booking ticket count fix completed',
        'fixed_count' => count($fixed),
        'error_count' => count($errors),
        'fixed_bookings' => $fixed,
        'errors' => $errors,
        'note' => 'Missing tickets (teenagers/university students) were added to adult_tickets count',
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'trace' => explode("\n", $e->getTraceAsString()),
    ], JSON_PRETTY_PRINT);
}
