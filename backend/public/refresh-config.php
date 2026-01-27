<?php
/**
 * Refresh Laravel Configuration Cache
 * This script clears and rebuilds the Laravel configuration cache
 */

echo "<h2>🔄 Laravel Configuration Refresh</h2>";

// Change to Laravel root directory
$laravelRoot = dirname(__DIR__);
chdir($laravelRoot);

echo "<h3>Current Directory:</h3>";
echo "<pre>" . getcwd() . "</pre>";

echo "<h3>Running Commands:</h3>";

// Clear config cache
echo "<h4>1. Clearing config cache:</h4>";
$output1 = shell_exec('php artisan config:clear 2>&1');
echo "<pre>$output1</pre>";

// Rebuild config cache
echo "<h4>2. Rebuilding config cache:</h4>";
$output2 = shell_exec('php artisan config:cache 2>&1');
echo "<pre>$output2</pre>";

// Clear route cache
echo "<h4>3. Clearing route cache:</h4>";
$output3 = shell_exec('php artisan route:clear 2>&1');
echo "<pre>$output3</pre>";

// Rebuild route cache
echo "<h4>4. Rebuilding route cache:</h4>";
$output4 = shell_exec('php artisan route:cache 2>&1');
echo "<pre>$output4</pre>";

echo "<h3>✅ Cache refresh completed!</h3>";
echo "<p><a href='check-billplz-config.php'>Check Billplz Configuration Again</a></p>";
?>