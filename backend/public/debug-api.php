<?php
/**
 * Simple debug API endpoint
 * Access via: https://admin.tfcmockup.com/debug-api.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    // Bootstrap Laravel
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    
    // Boot the application
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $app->instance('request', Illuminate\Http\Request::capture());
    $kernel->bootstrap();
    
    // Get events directly from database
    $events = DB::table('events')
        ->select([
            'id', 'title', 'description', 'location', 'event_date', 
            'adult_price as price', 'image', 'category', 'is_active'
        ])
        ->where('is_active', 1)
        ->get()
        ->map(function($event) {
            return [
                'id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'location' => $event->location,
                'event_date' => $event->event_date,
                'event_date_formatted' => date('M d, Y', strtotime($event->event_date)),
                'event_time_formatted' => '7:00 PM',
                'image_url' => $event->image 
                    ? url('storage/' . $event->image)
                    : 'https://picsum.photos/400/300?random=' . $event->id,
                'price' => $event->price ?? 50,
                'price_display' => 'RM' . ($event->price ?? 50),
                'category' => $event->category ?? 'Event',
                'is_booking_open' => true,
                'slug' => strtolower(str_replace(' ', '-', $event->title))
            ];
        });
    
    echo json_encode([
        'success' => true,
        'data' => $events,
        'total' => count($events),
        'message' => 'Events retrieved successfully',
        'debug_info' => [
            'timestamp' => date('Y-m-d H:i:s'),
            'app_env' => app()->environment(),
            'base_url' => url('/'),
            'storage_url' => url('storage/')
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
?>