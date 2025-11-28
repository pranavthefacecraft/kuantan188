<?php
/**
 * Simple deployment script to avoid common hosting issues
 */

echo "Starting deployment...\n";

// DEBUG: Show server paths and directory info
if (isset($_GET['debug']) || isset($_GET['info'])) {
    echo "\n=== SERVER PATH INFORMATION ===\n";
    echo "Current directory (__DIR__): " . __DIR__ . "\n";
    echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
    echo "Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
    echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "\nDirectory contents:\n";
    echo "Files in current directory:\n";
    foreach (scandir(__DIR__) as $file) {
        if ($file != '.' && $file != '..') {
            echo "- $file\n";
        }
    }
    echo "\nLaravel folders check:\n";
    echo "vendor/ exists: " . (is_dir(__DIR__ . '/vendor') ? 'YES' : 'NO') . "\n";
    echo "storage/ exists: " . (is_dir(__DIR__ . '/storage') ? 'YES' : 'NO') . "\n";
    echo "bootstrap/ exists: " . (is_dir(__DIR__ . '/bootstrap') ? 'YES' : 'NO') . "\n";
    echo "public/ exists: " . (is_dir(__DIR__ . '/public') ? 'YES' : 'NO') . "\n";
    echo "=== END DEBUG INFO ===\n\n";
}

// Install composer dependencies if vendor folder doesn't exist
if (!is_dir(__DIR__ . '/vendor') || !file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "Installing composer dependencies...\n";
    exec('composer install --no-dev --optimize-autoloader --no-interaction 2>&1', $composer_output, $composer_return);
    
    if ($composer_return === 0) {
        echo "✓ Composer dependencies installed successfully\n";
    } else {
        echo "⚠ Composer installation failed. Output:\n";
        echo implode("\n", $composer_output) . "\n";
    }
} else {
    echo "✓ Composer dependencies already installed\n";
}

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