<?php
echo "=== MANUAL UPLOAD TEST ===\n";
echo "If you can see this, manual upload works!\n";
echo "Current time: " . date('Y-m-d H:i:s') . "\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Parent directory: " . dirname(__DIR__) . "\n";
echo "\n=== SERVER STRUCTURE CHECK ===\n";

// Check current directory (should be public folder)
echo "\nCurrent directory contents (public folder):\n";
foreach (scandir(__DIR__) as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file" . (is_dir(__DIR__ . '/' . $file) ? '/' : '') . "\n";
    }
}

// Check parent directory (should be Laravel root)
echo "\nParent directory contents (Laravel root):\n";
foreach (scandir(dirname(__DIR__)) as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file" . (is_dir(dirname(__DIR__) . '/' . $file) ? '/' : '') . "\n";
    }
}

echo "\n=== LARAVEL STRUCTURE CHECK ===\n";
echo "Laravel root path: " . dirname(__DIR__) . "\n";
echo "composer.json exists: " . (file_exists(dirname(__DIR__) . '/composer.json') ? 'YES' : 'NO') . "\n";
echo "vendor/ exists: " . (is_dir(dirname(__DIR__) . '/vendor') ? 'YES' : 'NO') . "\n";
echo "app/ exists: " . (is_dir(dirname(__DIR__) . '/app') ? 'YES' : 'NO') . "\n";
echo "bootstrap/ exists: " . (is_dir(dirname(__DIR__) . '/bootstrap') ? 'YES' : 'NO') . "\n";
echo "storage/ exists: " . (is_dir(dirname(__DIR__) . '/storage') ? 'YES' : 'NO') . "\n";

echo "\n=== END TEST ===\n";
?>