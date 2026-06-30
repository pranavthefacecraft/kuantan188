<?php
/**
 * Quick cache clearing script
 * Access via: https://admin.tfcmockup.com/clear-view-cache.php
 */

// Change to Laravel root
chdir(__DIR__);

// Clear view cache
$viewPath = __DIR__ . '/storage/framework/views';
if (is_dir($viewPath)) {
    $files = glob($viewPath . '/*');
    foreach ($files as $file) {
        if (is_file($file)) {
            unlink($file);
        }
    }
    echo "✓ View cache cleared\n";
}

// Clear config cache
$configCache = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($configCache)) {
    unlink($configCache);
    echo "✓ Config cache cleared\n";
}

// Clear route cache
$routeCache = __DIR__ . '/bootstrap/cache/routes-v7.php';
if (file_exists($routeCache)) {
    unlink($routeCache);
    echo "✓ Route cache cleared\n";
}

echo "\n✓ All caches cleared successfully!\n";
echo "Please hard refresh your browser (Ctrl+Shift+R or Cmd+Shift+R)\n";
