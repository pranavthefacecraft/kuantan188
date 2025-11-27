<?php
/**
 * Simple deployment script to avoid common hosting issues
 */

echo "Starting deployment...\n";

// Create storage link manually if it doesn't exist
$public_storage = __DIR__ . '/public/storage';
$storage_public = __DIR__ . '/storage/app/public';

if (!file_exists($public_storage)) {
    if (function_exists('symlink')) {
        symlink($storage_public, $public_storage);
        echo "✓ Storage link created using symlink\n";
    } else {
        // Fallback: Create directory and copy files (for shared hosting)
        if (!is_dir($public_storage)) {
            mkdir($public_storage, 0755, true);
        }
        echo "✓ Storage directory created (symlink not available)\n";
        echo "⚠ Note: You may need to manually copy files from storage/app/public to public/storage\n";
    }
} else {
    echo "✓ Storage link already exists\n";
}

// Check database connection
try {
    require_once __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    echo "✓ Database connection successful\n";
    
    // Run optimizations
    exec('php artisan config:cache 2>&1', $output);
    echo "✓ Config cached\n";
    
    exec('php artisan route:cache 2>&1', $output);
    echo "✓ Routes cached\n";
    
    exec('php artisan view:cache 2>&1', $output);
    echo "✓ Views cached\n";
    
} catch (Exception $e) {
    echo "⚠ Error: " . $e->getMessage() . "\n";
}

echo "\nDeployment completed!\n";
echo "Backend URL: https://admin.tfcmockup.com\n";
echo "API Test: https://admin.tfcmockup.com/api/public/events\n";
?>