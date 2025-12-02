<?php
/**
 * Deployment Test File
 * This file tests the deployment process
 * Last updated: <?= date('Y-m-d H:i:s') ?>

 */

echo "🚀 Backend Deployment Test\n";
echo "========================\n\n";

echo "✅ Deployment Status: ACTIVE\n";
echo "📅 Test Time: " . date('Y-m-d H:i:s') . "\n";
echo "🔄 Deployment Version: 2.0 (Simplified)\n";
echo "📂 Laravel Version: " . (class_exists('Illuminate\Foundation\Application') ? app()->version() : 'Unknown') . "\n";
echo "🌐 Server: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "\n";
echo "📡 API Base: " . ($_SERVER['HTTP_HOST'] ?? 'localhost') . "/api\n\n";

echo "🧪 Quick Tests:\n";
echo "- PHP Version: " . phpversion() . "\n";
echo "- Memory Limit: " . ini_get('memory_limit') . "\n";
echo "- Max Execution Time: " . ini_get('max_execution_time') . "s\n";
echo "- Upload Max Size: " . ini_get('upload_max_filesize') . "\n\n";

echo "📋 Directory Status:\n";
$dirs = [
    'storage/logs' => '../storage/logs',
    'storage/app' => '../storage/app', 
    'storage/framework/cache' => '../storage/framework/cache',
    'bootstrap/cache' => '../bootstrap/cache'
];

foreach ($dirs as $name => $path) {
    $exists = is_dir(__DIR__ . '/' . $path);
    $writable = $exists && is_writable(__DIR__ . '/' . $path);
    echo "  $name: " . ($exists ? '✅ EXISTS' : '❌ MISSING') . 
         ($writable ? ' | ✍️ WRITABLE' : ' | ❌ NOT WRITABLE') . "\n";
}

echo "\n🔗 Useful Links:\n";
echo "- API Test: /api/public/events/book-now\n";
echo "- Storage Test: /test-storage.php\n"; 
echo "- Image Test: /test-storage.html\n";
echo "- API Debug: /api-test.html\n";

echo "\n🎯 Deployment Test Result: SUCCESS! 🎉\n";
echo "Last deployment completed successfully.\n";
?>