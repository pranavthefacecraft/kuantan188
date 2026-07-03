<?php
/**
 * PHP script to move ticket images from old location to new location
 * Access via browser: https://admin.tfcmockup.com/move-images.php
 * Or run via CLI: php public/move-images.php
 */

// Clear any opcode cache
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Define paths
$oldPath = __DIR__ . '/storage/tickets';
$newPath = __DIR__ . '/uploads/tickets';

$version = '2.0';
$lastUpdated = '2026-07-03 18:00:00';

echo "<h1>Ticket Image Migration v{$version}</h1>";
echo "<p style='color: #666;'>Last Updated: {$lastUpdated} UTC</p>";
echo "<p style='color: #666;'>Current Server Time: " . date('Y-m-d H:i:s') . "</p>";
echo "<hr>";
echo "<pre>";

// Check if old directory exists
if (!is_dir($oldPath)) {
    echo "❌ Old directory does not exist: $oldPath\n\n";
    
    // Check new directory
    if (is_dir($newPath)) {
        echo "✅ New directory exists: $newPath\n";
        echo "Files in new directory:\n";
        $files = scandir($newPath);
        foreach ($files as $file) {
            if ($file !== '.' && $file !== '..') {
                echo "  - $file\n";
            }
        }
    } else {
        echo "❌ New directory also does not exist: $newPath\n";
        echo "Creating new directory...\n";
        
        if (mkdir($newPath, 0755, true)) {
            echo "✅ Created: $newPath\n";
        } else {
            echo "❌ Failed to create directory\n";
        }
    }
    
    echo "\n=== DIAGNOSIS ===\n";
    echo "The issue is that image files don't exist in either location.\n";
    echo "This means:\n";
    echo "1. Images were never uploaded, OR\n";
    echo "2. Images are in a different location, OR\n";
    echo "3. Images were deleted\n\n";
    
    echo "Checking database for image paths:\n";
    try {
        include __DIR__ . '/../vendor/autoload.php';
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        
        $tickets = \App\Models\Ticket::whereNotNull('image_url')->get(['id', 'ticket_name', 'image_url']);
        
        echo "\nTickets with images in database: " . $tickets->count() . "\n\n";
        
        $foundCount = 0;
        $missingCount = 0;
        
        foreach ($tickets as $ticket) {
            echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
            echo "ID: {$ticket->id} | {$ticket->ticket_name}\n";
            echo "DB Path: {$ticket->image_url}\n";
            
            // Check multiple possible locations
            $possiblePaths = [
                __DIR__ . '/' . $ticket->image_url,
                __DIR__ . '/../' . $ticket->image_url,
                __DIR__ . '/../../' . $ticket->image_url,
            ];
            
            $found = false;
            foreach ($possiblePaths as $testPath) {
                if (file_exists($testPath)) {
                    echo "  ✅ FOUND at: $testPath\n";
                    echo "     Size: " . filesize($testPath) . " bytes\n";
                    echo "     Modified: " . date('Y-m-d H:i:s', filemtime($testPath)) . "\n";
                    $found = true;
                    $foundCount++;
                    break;
                }
            }
            
            if (!$found) {
                echo "  ❌ NOT FOUND\n";
                echo "     Expected: " . __DIR__ . '/' . $ticket->image_url . "\n";
                echo "     Full URL: https://admin.tfcmockup.com/{$ticket->image_url}\n";
                $missingCount++;
            }
            echo "\n";
        }
        
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "SUMMARY:\n";
        echo "  ✅ Files found: $foundCount\n";
        echo "  ❌ Files missing: $missingCount\n\n";
        
        if ($missingCount > 0) {
            echo "⚠️  ACTION REQUIRED:\n";
            echo "The image files need to be re-uploaded through the admin panel.\n";
            echo "Go to: https://admin.tfcmockup.com/admin/tickets\n";
            echo "Edit each ticket and upload the images again.\n\n";
        }
        
        // Check what directories exist
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "DIRECTORY STRUCTURE:\n";
        echo "Current directory: " . __DIR__ . "\n\n";
        
        $dirsToCheck = [
            'uploads/tickets',
            'uploads/events',
            'storage/tickets',
            'storage'
        ];
        
        foreach ($dirsToCheck as $dir) {
            $fullDir = __DIR__ . '/' . $dir;
            if (is_dir($fullDir)) {
                echo "✅ $dir/ exists\n";
                $files = array_diff(scandir($fullDir), ['.', '..']);
                $fileCount = count($files);
                echo "   Files: $fileCount\n";
                if ($fileCount > 0 && $fileCount <= 5) {
                    foreach ($files as $file) {
                        echo "   - $file\n";
                    }
                }
            } else {
                echo "❌ $dir/ does not exist\n";
            }
        }
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    
    exit;
}

// Create new directory if it doesn't exist
if (!is_dir($newPath)) {
    echo "Creating new directory: $newPath\n";
    if (mkdir($newPath, 0755, true)) {
        echo "✅ Created successfully\n\n";
    } else {
        echo "❌ Failed to create directory\n";
        exit;
    }
}

// Get files from old directory
$files = scandir($oldPath);
$imageFiles = array_filter($files, function($file) {
    return !in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
});

$fileCount = count($imageFiles);
echo "Found $fileCount image files in old location\n\n";

if ($fileCount === 0) {
    echo "⚠️  No files to move\n";
    exit;
}

echo "Files to be moved:\n";
foreach ($imageFiles as $file) {
    echo "  - $file\n";
}

echo "\nMoving files...\n\n";

$successCount = 0;
$errorCount = 0;

foreach ($imageFiles as $file) {
    $oldFile = $oldPath . '/' . $file;
    $newFile = $newPath . '/' . $file;
    
    if (copy($oldFile, $newFile)) {
        chmod($newFile, 0644);
        echo "✅ Copied: $file\n";
        $successCount++;
    } else {
        echo "❌ Failed: $file\n";
        $errorCount++;
    }
}

echo "\n=== Summary ===\n";
echo "✅ Successfully copied: $successCount files\n";
echo "❌ Failed: $errorCount files\n";

if ($successCount > 0) {
    echo "\nFiles in new location:\n";
    $newFiles = scandir($newPath);
    foreach ($newFiles as $file) {
        if ($file !== '.' && $file !== '..') {
            echo "  - $file\n";
        }
    }
    
    echo "\n⚠️  Old files still exist in: $oldPath\n";
    echo "You can manually delete them after verifying images work.\n";
}

echo "\n=== Complete ===\n";
echo "</pre>";
