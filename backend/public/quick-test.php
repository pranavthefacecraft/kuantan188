<?php
/**
 * Quick Configuration & Connection Tests
 * Non-blocking version that handles timeouts gracefully
 */

set_time_limit(60); // Set maximum execution time

echo "<h2>🔄 Quick Laravel Tests</h2>";

// Change to Laravel root directory
$laravelRoot = dirname(__DIR__);
chdir($laravelRoot);

echo "<h3>Current Directory:</h3>";
echo "<pre>" . getcwd() . "</pre>";

// Function to run command with timeout
function runCommandWithTimeout($command, $timeout = 15) {
    $startTime = time();
    echo "<p>Running: <code>$command</code></p>";
    
    $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"], 
        2 => ["pipe", "w"]
    ];
    
    $process = proc_open($command, $descriptors, $pipes);
    
    if (!is_resource($process)) {
        return "Failed to start process";
    }
    
    fclose($pipes[0]);
    
    $output = "";
    $error = "";
    
    while (time() - $startTime < $timeout) {
        $status = proc_get_status($process);
        if (!$status['running']) {
            $output .= stream_get_contents($pipes[1]);
            $error .= stream_get_contents($pipes[2]);
            break;
        }
        usleep(100000); // 0.1 second
    }
    
    fclose($pipes[1]);
    fclose($pipes[2]);
    
    $exitCode = proc_close($process);
    
    if (time() - $startTime >= $timeout) {
        return "Command timed out after {$timeout} seconds";
    }
    
    return $output . ($error ? "\nError: " . $error : "");
}

// Test Environment Variables First (Fast)
echo "<h3>🔧 Environment Variables Check:</h3>";
$envVars = [
    'DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME',
    'BILLPLZ_API_KEY', 'BILLPLZ_COLLECTION_ID', 'BILLPLZ_ENVIRONMENT'
];

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Variable</th><th>Status</th><th>Value</th></tr>";

$dbConfigured = true;
$billplzConfigured = true;

foreach ($envVars as $var) {
    $value = $_ENV[$var] ?? getenv($var) ?? '';
    $isSet = $value !== '';
    
    if (strpos($var, 'DB_') === 0 && !$isSet) $dbConfigured = false;
    if (strpos($var, 'BILLPLZ_') === 0 && !$isSet) $billplzConfigured = false;
    
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

// Quick config clear (without cache rebuild to avoid DB issues)
echo "<h3>⚡ Quick Config Clear:</h3>";
$configClear = runCommandWithTimeout('php artisan config:clear', 10);
echo "<pre>$configClear</pre>";

// Test Database Connection (Simple)
echo "<h3>🗄️ Database Connection Test:</h3>";
if ($dbConfigured) {
    try {
        $dbHost = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
        $dbPort = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?? 3306;
        $dbName = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $dbUser = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME');
        $dbPass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
        
        echo "<p>Testing connection to: <strong>{$dbUser}@{$dbHost}:{$dbPort}/{$dbName}</strong></p>";
        
        $pdo = new PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName}", $dbUser, $dbPass, [
            PDO::ATTR_TIMEOUT => 5,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        echo "<p style='color: green;'>✅ <strong>Database connection successful!</strong></p>";
        
        // Test a simple query
        $result = $pdo->query("SELECT COUNT(*) as count FROM events")->fetch();
        echo "<p>Events in database: <strong>{$result['count']}</strong></p>";
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Database connection failed:</strong> " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Database environment variables not configured</p>";
}

// Test Billplz via HTTP (Faster than artisan)
echo "<h3>💳 Billplz Connection Test:</h3>";
if ($billplzConfigured) {
    try {
        $billplzUrl = "https://admin.tfcmockup.com/api/public/payment/billplz/test";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'header' => 'Accept: application/json'
            ]
        ]);
        
        $result = file_get_contents($billplzUrl, false, $context);
        
        if ($result) {
            $data = json_decode($result, true);
            if ($data && isset($data['success']) && $data['success']) {
                echo "<p style='color: green;'>✅ <strong>Billplz connection successful!</strong></p>";
                echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
            } else {
                echo "<p style='color: red;'>❌ <strong>Billplz test failed:</strong></p>";
                echo "<pre>$result</pre>";
            }
        } else {
            echo "<p style='color: red;'>❌ <strong>Could not reach Billplz API</strong></p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Billplz test error:</strong> " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Billplz environment variables not configured</p>";
}

echo "<h3>📊 Summary:</h3>";
echo "<ul>";
if ($dbConfigured) {
    echo "<li style='color: green;'>✅ Database credentials configured</li>";
} else {
    echo "<li style='color: red;'>❌ Database credentials missing</li>";
}

if ($billplzConfigured) {
    echo "<li style='color: green;'>✅ Billplz credentials configured</li>";
} else {
    echo "<li style='color: red;'>❌ Billplz credentials missing</li>";
}
echo "</ul>";

echo "<h3>🔗 Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='check-billplz-config.php'>Detailed Billplz Configuration</a></li>";
echo "<li><a href='debug-api.php'>API Debug Information</a></li>";
echo "<li><a href='https://tickets.tfcmockup.com' target='_blank'>Test Frontend</a></li>";
echo "</ul>";

$endTime = microtime(true);
$startTime = $_SERVER['REQUEST_TIME_FLOAT'];
$executionTime = round(($endTime - $startTime), 2);
echo "<p><small>Execution time: {$executionTime} seconds</small></p>";
?>