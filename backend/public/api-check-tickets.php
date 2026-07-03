<?php
header('Content-Type: application/json');

$response = [
    'status' => 'OK',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => phpversion(),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown',
    'script_filename' => __FILE__,
    'working_directory' => getcwd(),
];

// Check ticket directories
$response['directories'] = [];

$dirs = ['uploads/tickets', 'uploads/events', 'storage/tickets'];
foreach ($dirs as $dir) {
    $fullPath = __DIR__ . '/' . $dir;
    $response['directories'][$dir] = [
        'exists' => is_dir($fullPath),
        'path' => $fullPath,
        'writable' => is_dir($fullPath) ? is_writable($fullPath) : false,
    ];
}

// Check tickets from database
try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $tickets = \App\Models\Ticket::whereNotNull('image_url')
        ->get(['id', 'ticket_name', 'image_url'])
        ->map(function($ticket) {
            $imagePath = __DIR__ . '/' . $ticket->image_url;
            return [
                'id' => $ticket->id,
                'name' => $ticket->ticket_name,
                'db_path' => $ticket->image_url,
                'full_path' => $imagePath,
                'exists' => file_exists($imagePath),
                'size' => file_exists($imagePath) ? filesize($imagePath) : 0,
            ];
        });
    
    $response['tickets'] = $tickets;
    $response['total_tickets'] = $tickets->count();
    $response['missing_files'] = $tickets->filter(fn($t) => !$t['exists'])->count();
    
} catch (Exception $e) {
    $response['database_error'] = $e->getMessage();
}

echo json_encode($response, JSON_PRETTY_PRINT);
