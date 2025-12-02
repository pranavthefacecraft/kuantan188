<?php
/**
 * Storage Directory and Permissions Test
 * Upload this file to your Laravel root directory and run it
 */

echo "🧪 Laravel Storage Directory Test\n";
echo "================================\n\n";

// Get the current directory (should be Laravel root)
$laravelRoot = __DIR__;
echo "📁 Laravel Root: $laravelRoot\n\n";

// Define directories to check
$directories = [
    'storage' => $laravelRoot . '/storage',
    'storage/app' => $laravelRoot . '/storage/app',
    'storage/app/public' => $laravelRoot . '/storage/app/public',
    'storage/app/public/uploads' => $laravelRoot . '/storage/app/public/uploads',
    'storage/app/public/uploads/events' => $laravelRoot . '/storage/app/public/uploads/events',
    'storage/framework' => $laravelRoot . '/storage/framework',
    'storage/framework/cache' => $laravelRoot . '/storage/framework/cache',
    'storage/framework/sessions' => $laravelRoot . '/storage/framework/sessions',
    'storage/framework/views' => $laravelRoot . '/storage/framework/views',
    'storage/logs' => $laravelRoot . '/storage/logs',
    'bootstrap/cache' => $laravelRoot . '/bootstrap/cache',
    'public/storage' => $laravelRoot . '/public/storage'
];

echo "📋 Directory Status Check:\n";
echo "--------------------------\n";

foreach ($directories as $name => $path) {
    echo sprintf("%-30s", $name . ':');
    
    if (file_exists($path)) {
        echo "✅ EXISTS";
        
        if (is_dir($path)) {
            echo " | 📁 DIR";
            
            // Check if writable
            if (is_writable($path)) {
                echo " | ✍️ WRITABLE";
            } else {
                echo " | ❌ NOT WRITABLE";
            }
            
            // Show permissions
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            echo " | 🔒 $perms";
            
        } else {
            echo " | ⚠️ NOT A DIRECTORY";
        }
    } else {
        echo "❌ MISSING";
    }
    
    echo "\n";
}

echo "\n📸 Image Files Check:\n";
echo "--------------------\n";

// Check for actual uploaded images
$uploadsDir = $laravelRoot . '/storage/app/public/uploads/events';
if (is_dir($uploadsDir)) {
    $images = glob($uploadsDir . '/*.{jpg,jpeg,png,gif,webp}', GLOB_BRACE);
    
    if (!empty($images)) {
        echo "Found " . count($images) . " image files:\n";
        foreach ($images as $image) {
            $filename = basename($image);
            $size = filesize($image);
            $readable = is_readable($image) ? '✅ READABLE' : '❌ NOT READABLE';
            echo "  📷 $filename ($size bytes) - $readable\n";
            
            // Check if accessible via web
            $webPath = '/storage/uploads/events/' . $filename;
            echo "     🌐 Web path: $webPath\n";
        }
    } else {
        echo "❌ No image files found in uploads/events directory\n";
    }
} else {
    echo "❌ Events upload directory does not exist\n";
}

echo "\n🔗 Storage Symlink Check:\n";
echo "------------------------\n";

$publicStorage = $laravelRoot . '/public/storage';
if (is_link($publicStorage)) {
    echo "✅ Symlink exists: public/storage -> " . readlink($publicStorage) . "\n";
    
    if (file_exists($publicStorage)) {
        echo "✅ Symlink target is accessible\n";
    } else {
        echo "❌ Symlink target is broken\n";
    }
} elseif (is_dir($publicStorage)) {
    echo "⚠️ public/storage exists as directory (not symlink)\n";
} else {
    echo "❌ No storage symlink found\n";
    echo "💡 Run: php artisan storage:link\n";
}

echo "\n🧪 Write Test:\n";
echo "--------------\n";

// Test writing to storage directories
$testDirs = [
    $laravelRoot . '/storage/logs',
    $laravelRoot . '/storage/app/public/uploads/events',
    $laravelRoot . '/bootstrap/cache'
];

foreach ($testDirs as $dir) {
    $dirName = str_replace($laravelRoot . '/', '', $dir);
    echo "Testing write to $dirName: ";
    
    if (is_dir($dir) && is_writable($dir)) {
        $testFile = $dir . '/test_' . time() . '.txt';
        $testContent = 'Laravel storage test - ' . date('Y-m-d H:i:s');
        
        if (file_put_contents($testFile, $testContent)) {
            echo "✅ SUCCESS";
            
            // Clean up test file
            unlink($testFile);
        } else {
            echo "❌ FAILED TO WRITE";
        }
    } else {
        echo "❌ DIRECTORY NOT WRITABLE";
    }
    
    echo "\n";
}

echo "\n🌐 Web Accessibility Test:\n";
echo "-------------------------\n";

// Get the domain from current request
$domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'your-domain.com';
$protocol = isset($_SERVER['HTTPS']) ? 'https' : 'http';

echo "Current domain: $protocol://$domain\n";
echo "Testing image URLs that should work:\n";

// Sample image paths based on your API response
$sampleImages = [
    'uploads/events/1764698152_kuantan4.png',
    'uploads/events/1764697922_unnamed (2).jpg',
    'uploads/events/1764697932_unnamed.jpg',
    'uploads/events/1764698137_kuantan1.png'
];

foreach ($sampleImages as $imagePath) {
    $localFile = $laravelRoot . '/storage/app/public/' . $imagePath;
    $webUrl = "$protocol://$domain/storage/$imagePath";
    
    echo "\n📷 $imagePath:\n";
    echo "  📁 Local file: " . (file_exists($localFile) ? '✅ EXISTS' : '❌ MISSING') . "\n";
    echo "  🌐 Web URL: $webUrl\n";
    echo "  🔗 Should be accessible at: $webUrl\n";
}

echo "\n💡 Quick Fixes:\n";
echo "--------------\n";
echo "1. Create missing directories: mkdir -p storage/app/public/uploads/events\n";
echo "2. Set permissions: chmod 755 storage/ bootstrap/ -R\n";
echo "3. Create storage link: php artisan storage:link\n";
echo "4. Clear cache: php artisan cache:clear && php artisan config:clear\n";

echo "\n✨ Test completed at: " . date('Y-m-d H:i:s') . "\n";
?>