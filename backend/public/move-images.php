<?php
/**
 * PHP script to move ticket images from old location to new location
 * Access via browser: https://admin.tfcmockup.com/move-images.php
 * Or run via CLI: php public/move-images.php
 */

// Define paths
$oldPath = __DIR__ . '/storage/tickets';
$newPath = __DIR__ . '/uploads/tickets';

echo "<h1>Ticket Image Migration</h1>";
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
    include __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $tickets = \App\Models\Ticket::whereNotNull('image_url')->get(['id', 'ticket_name', 'image_url']);
    
    echo "\nTickets with images in database:\n";
    foreach ($tickets as $ticket) {
        echo "ID: {$ticket->id} | {$ticket->ticket_name}\n";
        echo "  Path: {$ticket->image_url}\n";
        
        $fullPath = __DIR__ . '/' . $ticket->image_url;
        if (file_exists($fullPath)) {
            echo "  ✅ File exists at: $fullPath\n";
        } else {
            echo "  ❌ File NOT found at: $fullPath\n";
        }
        echo "\n";
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
