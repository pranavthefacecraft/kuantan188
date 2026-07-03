<?php
/**
 * Simple diagnostic to check ticket images
 */

// Disable output buffering
if (ob_get_level()) ob_end_clean();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ticket Image Diagnostic</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .info { color: #007bff; }
        .warning { color: #ffc107; }
        pre { background: white; padding: 15px; border: 1px solid #ddd; border-radius: 4px; }
        table { background: white; border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>
<h1>🔍 Ticket Image Diagnostic v3.0</h1>
<p>Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
<hr>

<?php
echo "<h2>1. Checking Database Connection...</h2>\n";

try {
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo "<p class='success'>✅ Database connected successfully</p>\n";
    
    echo "<h2>2. Fetching Tickets with Images...</h2>\n";
    
    $tickets = \App\Models\Ticket::whereNotNull('image_url')
        ->orderBy('id')
        ->get(['id', 'ticket_name', 'image_url', 'is_active']);
    
    echo "<p class='info'>📊 Found {$tickets->count()} tickets with image URLs in database</p>\n";
    
    if ($tickets->count() === 0) {
        echo "<p class='warning'>⚠️ No tickets have image URLs in the database</p>\n";
    } else {
        echo "<h2>3. Checking Each Ticket Image...</h2>\n";
        echo "<table>\n";
        echo "<tr>
                <th>ID</th>
                <th>Ticket Name</th>
                <th>Active</th>
                <th>Database Path</th>
                <th>File Status</th>
                <th>File Info</th>
              </tr>\n";
        
        $foundCount = 0;
        $missingCount = 0;
        
        foreach ($tickets as $ticket) {
            $imagePath = $ticket->image_url;
            $fullPath = __DIR__ . '/' . $imagePath;
            
            $fileExists = file_exists($fullPath);
            $statusClass = $fileExists ? 'success' : 'error';
            $statusIcon = $fileExists ? '✅' : '❌';
            
            if ($fileExists) {
                $foundCount++;
                $fileSize = filesize($fullPath);
                $fileTime = date('Y-m-d H:i:s', filemtime($fullPath));
                $fileInfo = number_format($fileSize / 1024, 2) . " KB<br>Modified: {$fileTime}";
            } else {
                $missingCount++;
                $fileInfo = 'File not found';
            }
            
            $activeStatus = $ticket->is_active ? '✅ Yes' : '❌ No';
            
            echo "<tr>
                    <td>{$ticket->id}</td>
                    <td>{$ticket->ticket_name}</td>
                    <td>{$activeStatus}</td>
                    <td><code>{$imagePath}</code></td>
                    <td class='{$statusClass}'>{$statusIcon}</td>
                    <td>{$fileInfo}</td>
                  </tr>\n";
        }
        
        echo "</table>\n";
        
        echo "<h2>4. Summary</h2>\n";
        echo "<ul>\n";
        echo "<li class='success'>✅ Files Found: {$foundCount}</li>\n";
        echo "<li class='error'>❌ Files Missing: {$missingCount}</li>\n";
        echo "</ul>\n";
    }
    
    echo "<h2>5. Directory Structure</h2>\n";
    echo "<table>\n";
    echo "<tr><th>Directory</th><th>Exists</th><th>Permissions</th><th>File Count</th></tr>\n";
    
    $directories = [
        'uploads/tickets',
        'uploads/events',
        'storage/tickets',
        'storage',
    ];
    
    foreach ($directories as $dir) {
        $fullDir = __DIR__ . '/' . $dir;
        $exists = is_dir($fullDir);
        $statusClass = $exists ? 'success' : 'error';
        $statusIcon = $exists ? '✅ Yes' : '❌ No';
        
        if ($exists) {
            $perms = substr(sprintf('%o', fileperms($fullDir)), -4);
            $files = array_diff(scandir($fullDir), ['.', '..']);
            $fileCount = count($files);
        } else {
            $perms = 'N/A';
            $fileCount = 0;
        }
        
        echo "<tr>
                <td><code>{$dir}/</code></td>
                <td class='{$statusClass}'>{$statusIcon}</td>
                <td>{$perms}</td>
                <td>{$fileCount}</td>
              </tr>\n";
    }
    
    echo "</table>\n";
    
    echo "<h2>6. Current Directory Info</h2>\n";
    echo "<pre>\n";
    echo "Script Location: " . __DIR__ . "\n";
    echo "Working Directory: " . getcwd() . "\n";
    echo "</pre>\n";
    
    if ($missingCount > 0) {
        echo "<h2>⚠️ Action Required</h2>\n";
        echo "<div style='background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 4px;'>\n";
        echo "<p><strong>All {$missingCount} ticket image files are missing from the server.</strong></p>\n";
        echo "<p>You need to re-upload the images through the admin panel:</p>\n";
        echo "<ol>\n";
        echo "<li>Go to: <a href='https://admin.tfcmockup.com/admin/tickets' target='_blank'>Admin Tickets Page</a></li>\n";
        echo "<li>Edit each ticket listed above</li>\n";
        echo "<li>Upload the ticket image</li>\n";
        echo "<li>Save the ticket</li>\n";
        echo "</ol>\n";
        echo "<p>New uploads will automatically save to <code>uploads/tickets/</code> and work correctly.</p>\n";
        echo "</div>\n";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>\n";
}
?>

</body>
</html>
