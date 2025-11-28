<?php
// Simple log viewer that doesn't depend on Laravel routing
if (!file_exists(__DIR__.'/../storage/logs/laravel.log')) {
    echo json_encode(['error' => 'Log file not found']);
    exit;
}

$lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 50;
$content = file(__DIR__.'/../storage/logs/laravel.log');
$totalLines = count($content);

// Get the last N lines
$logLines = array_slice($content, -$lines);

header('Content-Type: application/json');
echo json_encode([
    'total_lines' => $totalLines,
    'showing_lines' => count($logLines),
    'last_lines' => array_map('trim', $logLines),
    'timestamp' => date('Y-m-d H:i:s')
], JSON_PRETTY_PRINT);
?>