<?php
/**
 * Refresh Laravel Configuration Cache & Test Connections
 * This script clears and rebuilds the Laravel configuration cache
 * and tests database and Billplz connectivity
 */

echo "<h2>🔄 Laravel Configuration Refresh & Tests</h2>";

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

// Test Database Connection
echo "<h3>🗄️ Database Connection Test:</h3>";
try {
    $dbTest = shell_exec('php artisan tinker --execute="echo DB::connection()->getPdo() ? \'Database Connected Successfully\' : \'Database Connection Failed\'; echo PHP_EOL;" 2>&1');
    if (strpos($dbTest, 'Database Connected Successfully') !== false) {
        echo "<p style='color: green;'>✅ <strong>Database connection successful!</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>Database connection failed:</strong></p>";
        echo "<pre>$dbTest</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Database test error:</strong> " . $e->getMessage() . "</p>";
}

// Test Billplz Configuration
echo "<h3>💳 Billplz Configuration Test:</h3>";
try {
    // Test if Billplz API endpoint responds
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/api/public/payment/billplz/test');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept: application/json']);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $billplzTest = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($billplzTest, true);
        if ($result && isset($result['success']) && $result['success']) {
            echo "<p style='color: green;'>✅ <strong>Billplz configuration successful!</strong></p>";
            echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ <strong>Billplz configuration failed:</strong></p>";
            echo "<pre>$billplzTest</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ <strong>Billplz API not reachable (HTTP $httpCode)</strong></p>";
        echo "<pre>$billplzTest</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ <strong>Billplz test error:</strong> " . $e->getMessage() . "</p>";
}

// Test Environment Variables
echo "<h3>🔧 Environment Variables Check:</h3>";
$envVars = [
    'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME',
    'BILLPLZ_API_KEY', 'BILLPLZ_COLLECTION_ID', 'BILLPLZ_ENVIRONMENT'
];

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Variable</th><th>Status</th><th>Value</th></tr>";

foreach ($envVars as $var) {
    $value = getenv($var);
    $isSet = $value !== false && $value !== '';
    
    echo "<tr>";
    echo "<td><strong>$var</strong></td>";
    
    if ($isSet) {
        echo "<td style='color: green;'>✅ SET</td>";
        // Mask sensitive values
        if (strpos($var, 'PASSWORD') !== false || strpos($var, 'KEY') !== false) {
            $maskedValue = substr($value, 0, 8) . '***';
        } else {
            $maskedValue = $value;
        }
        echo "<td>$maskedValue</td>";
    } else {
        echo "<td style='color: red;'>❌ MISSING</td>";
        echo "<td>NOT SET</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<h3>🔗 Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='check-billplz-config.php'>Check Billplz Configuration Details</a></li>";
echo "<li><a href='debug-api.php'>API Debug Information</a></li>";
echo "<li><a href='log-viewer.html'>View Application Logs</a></li>";
echo "</ul>";

echo "<h3>📝 Next Steps:</h3>";
echo "<ol>";
echo "<li>If database connection failed, check your DB credentials in .env file</li>";
echo "<li>If Billplz test failed, verify your Billplz API keys and collection ID</li>";
echo "<li>Test the full payment flow at <a href='https://tickets.tfcmockup.com' target='_blank'>tickets.tfcmockup.com</a></li>";
echo "</ol>";
?>