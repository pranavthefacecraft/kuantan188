<?php
/**
 * Simple deployment script to avoid common hosting issues
 */

echo "Starting deployment...\n";

// DEBUG: Show server paths and directory info FIRST
echo "\n=== SERVER PATH INFORMATION ===\n";
echo "Current directory (__DIR__): " . __DIR__ . "\n";
echo "Parent directory: " . dirname(__DIR__) . "\n";
echo "Document root: " . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'N/A') . "\n";
echo "Script filename: " . (isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : 'N/A') . "\n";
echo "Request URI: " . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'N/A') . "\n";
echo "\nDirectory contents (current - public):\n";
foreach (scandir(__DIR__) as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file\n";
    }
}
echo "\nDirectory contents (parent - Laravel root):\n";
foreach (scandir(dirname(__DIR__)) as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file\n";
    }
}
echo "\nLaravel folders check (from parent):\n";
echo "vendor/ exists: " . (is_dir(dirname(__DIR__) . '/vendor') ? 'YES' : 'NO') . "\n";
echo "storage/ exists: " . (is_dir(dirname(__DIR__) . '/storage') ? 'YES' : 'NO') . "\n";
echo "bootstrap/ exists: " . (is_dir(dirname(__DIR__) . '/bootstrap') ? 'YES' : 'NO') . "\n";
echo "composer.json exists: " . (file_exists(dirname(__DIR__) . '/composer.json') ? 'YES' : 'NO') . "\n";
echo "=== END DEBUG INFO ===\n\n";

// Only proceed with composer if not in debug mode
if (isset($_GET['debug']) || isset($_GET['info'])) {
    echo "Debug mode - skipping composer installation.\n";
    echo "Visit without ?debug=1 parameter to run full deployment.\n";
    exit;
}

// Install composer dependencies if vendor folder doesn't exist (check parent directory)
$laravel_root = dirname(__DIR__);
if (!is_dir($laravel_root . '/vendor') || !file_exists($laravel_root . '/vendor/autoload.php')) {
    echo "Installing composer dependencies...\n";
    exec('cd ' . $laravel_root . ' && composer install --no-dev --optimize-autoloader --no-interaction 2>&1', $composer_output, $composer_return);
    
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
$public_storage = __DIR__ . '/storage';
$storage_public = $laravel_root . '/storage/app/public';

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
    require_once $laravel_root . '/vendor/autoload.php';
    $app = require_once $laravel_root . '/bootstrap/app.php';
    
    echo "✓ Database connection successful\n";
    
    // Run optimizations
    exec('cd ' . $laravel_root . ' && php artisan config:cache 2>&1', $output);
    echo "✓ Config cached\n";
    
    exec('cd ' . $laravel_root . ' && php artisan route:cache 2>&1', $output);
    echo "✓ Routes cached\n";
    
    exec('cd ' . $laravel_root . ' && php artisan view:cache 2>&1', $output);
    echo "✓ Views cached\n";
    
} catch (Exception $e) {
    echo "⚠ Error: " . $e->getMessage() . "\n";
}

echo "\nDeployment completed!\n";
echo "Backend URL: https://admin.tfcmockup.com\n";
echo "API Test: https://admin.tfcmockup.com/api/public/events\n";
?>