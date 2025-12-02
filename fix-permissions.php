<?php
/**
 * Laravel Permission Fix Script
 * Upload this file to your server root and run via browser: domain.com/fix-permissions.php
 * Then delete this file after running it successfully
 */

echo "<h2>Laravel Permission Fix</h2>\n";

$directories = [
    'bootstrap/cache',
    'storage',
    'storage/app',
    'storage/framework',
    'storage/framework/cache',
    'storage/framework/sessions', 
    'storage/framework/views',
    'storage/logs'
];

$success = true;

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "✅ Created directory: $dir<br>\n";
        } else {
            echo "❌ Failed to create directory: $dir<br>\n";
            $success = false;
        }
    } else {
        echo "✅ Directory exists: $dir<br>\n";
    }
    
    // Try to set permissions
    if (is_dir($dir)) {
        if (chmod($dir, 0755)) {
            echo "✅ Set permissions for: $dir<br>\n";
        } else {
            echo "⚠️ Could not set permissions for: $dir (may not have permission)<br>\n";
        }
    }
}

// Create .gitkeep files to ensure directories stay
$gitkeepFiles = [
    'bootstrap/cache/.gitkeep',
    'storage/app/.gitkeep',
    'storage/framework/cache/.gitkeep',
    'storage/framework/sessions/.gitkeep',
    'storage/framework/views/.gitkeep',
    'storage/logs/.gitkeep'
];

foreach ($gitkeepFiles as $file) {
    if (!file_exists($file)) {
        if (file_put_contents($file, '')) {
            echo "✅ Created: $file<br>\n";
        } else {
            echo "❌ Failed to create: $file<br>\n";
            $success = false;
        }
    }
}

echo "<hr>\n";

if ($success) {
    echo "<h3 style='color: green;'>✅ All directories and permissions fixed!</h3>\n";
    echo "<p>You can now try running Laravel commands like:</p>\n";
    echo "<ul>\n";
    echo "<li><code>php artisan config:clear</code></li>\n";
    echo "<li><code>php artisan optimize:clear</code></li>\n";
    echo "<li><code>php artisan config:cache</code></li>\n";
    echo "</ul>\n";
    echo "<p><strong>Important:</strong> Delete this file (fix-permissions.php) after use for security!</p>\n";
} else {
    echo "<h3 style='color: red;'>❌ Some operations failed</h3>\n";
    echo "<p>You may need to create directories manually via cPanel File Manager or contact your hosting provider.</p>\n";
}

echo "<hr>\n";
echo "<h3>Current Directory Structure:</h3>\n";
echo "<pre>\n";

function listDirectory($dir, $prefix = '') {
    if (!is_dir($dir)) return;
    
    $files = scandir($dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $path = $dir . '/' . $file;
        echo $prefix . $file;
        
        if (is_dir($path)) {
            echo " (directory)\n";
            if (in_array($file, ['bootstrap', 'storage'])) {
                listDirectory($path, $prefix . '  ');
            }
        } else {
            echo "\n";
        }
    }
}

listDirectory('.');
echo "</pre>\n";
?>