<?php
// Simple log clearer that doesn't depend on Laravel routing
$logFile = __DIR__.'/../storage/logs/laravel.log';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Only POST method allowed']);
    exit;
}

if (!file_exists($logFile)) {
    echo json_encode(['error' => 'Log file not found']);
    exit;
}

// Clear the log file
$result = file_put_contents($logFile, '');

if ($result !== false) {
    echo json_encode([
        'message' => 'Logs cleared successfully!',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
} else {
    echo json_encode([
        'error' => 'Failed to clear logs - check file permissions',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
?>