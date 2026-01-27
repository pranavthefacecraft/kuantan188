<?php
/**
 * Quick Configuration & Connection Tests
 * Enhanced version with comprehensive diagnostics and deployment checks
 * Non-blocking version that handles timeouts gracefully
 */

set_time_limit(90); // Set maximum execution time

// Enhanced styling
echo "<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 20px; background: #f5f5f5; }
    .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h2 { color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; }
    h3 { color: #34495e; margin-top: 25px; }
    .success { color: #27ae60; font-weight: bold; }
    .warning { color: #f39c12; font-weight: bold; }
    .error { color: #e74c3c; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; }
    th, td { padding: 8px 12px; text-align: left; border: 1px solid #bdc3c7; }
    th { background: #ecf0f1; }
    pre { background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 5px; overflow-x: auto; }
    .status-ok { background: #d5f4e6; }
    .status-warning { background: #fef9e7; }
    .status-error { background: #fadbd8; }
</style>";

echo "<div class='container'>";
echo "<h2>🚀 Kuantan188 System Diagnostics</h2>";
echo "<p><strong>Test Date:</strong> " . date('Y-m-d H:i:s') . "</p>";

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
        return "Failed to start process.";
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
    $isEmpty = $value === '';
    
    if (strpos($var, 'DB_') === 0 && (!$isSet || $isEmpty)) $dbConfigured = false;
    if (strpos($var, 'BILLPLZ_') === 0 && (!$isSet || $isEmpty)) $billplzConfigured = false;
    
    echo "<tr>";
    echo "<td><strong>$var</strong></td>";
    
    if ($isEmpty) {
        echo "<td style='color: orange;'>⚠️ EMPTY</td>";
        echo "<td>SET BUT EMPTY</td>";
    } elseif ($isSet) {
        echo "<td class='status-ok success'>✅ SET</td>";
        // Mask sensitive values
        if (strpos($var, 'PASSWORD') !== false || strpos($var, 'KEY') !== false) {
            $maskedValue = strlen($value) > 8 ? substr($value, 0, 8) . '***' : '***';
        } else {
            $maskedValue = $value;
        }
        echo "<td>$maskedValue</td>";
    } else {
        echo "<td class='status-error error'>❌ MISSING</td>";
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
        
        $response = @file_get_contents($billplzUrl, false, $context);
        
        if ($response !== false) {
            echo "<p style='color: green;'>✅ <strong>Billplz API test endpoint reachable</strong></p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "</pre>";
        } else {
            echo "<p style='color: red;'>❌ <strong>Billplz API test failed</strong> - Check API configuration</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Billplz API error:</strong> " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Billplz environment variables not configured</p>";
}

// Deployment Status Check
echo "<h3>🚀 Deployment Status Check:</h3>";
$deploymentFiles = [
    'composer.json' => 'Composer configuration',
    '.env' => 'Environment configuration', 
    'artisan' => 'Laravel Artisan CLI',
    'public/index.php' => 'Laravel entry point',
    'app/Http/Controllers/BillplzController.php' => 'Payment controller',
    'routes/api.php' => 'API routes',
    'config/cors.php' => 'CORS configuration'
];

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>File/Component</th><th>Status</th><th>Description</th></tr>";

foreach ($deploymentFiles as $file => $description) {
    $exists = file_exists($file);
    $status = $exists ? "✅ Found" : "❌ Missing";
    $rowClass = $exists ? "status-ok" : "status-error";
    
    echo "<tr class='$rowClass'>";
    echo "<td><strong>$file</strong></td>";
    echo "<td>$status</td>";
    echo "<td>$description</td>";
    echo "</tr>";
}
echo "</table>";

// Laravel Optimization Status
echo "<h3>⚡ Laravel Optimization Status:</h3>";
$cacheFiles = [
    'bootstrap/cache/config.php' => 'Config cache',
    'bootstrap/cache/routes-v7.php' => 'Routes cache', 
    'bootstrap/cache/services.php' => 'Services cache'
];

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Cache Type</th><th>Status</th><th>File</th></tr>";

foreach ($cacheFiles as $file => $type) {
    $exists = file_exists($file);
    $status = $exists ? "✅ Cached" : "⚠️ Not Cached";
    $rowClass = $exists ? "status-ok" : "status-warning";
    
    echo "<tr class='$rowClass'>";
    echo "<td><strong>$type</strong></td>";
    echo "<td>$status</td>";
    echo "<td><code>$file</code></td>";
    echo "</tr>";
}
echo "</table>";

if (!file_exists('bootstrap/cache/config.php')) {
    echo "<p class='warning'>💡 <strong>Tip:</strong> Run <code>php artisan config:cache</code> to improve performance</p>";
}

// FTP Connection Test (If credentials available)
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

// Test FTP Connection (GitHub Secrets Simulation)
echo "<h3>📡 FTP Connection Test (FileZilla Settings):</h3>";
echo "<p><strong>Note:</strong> Testing FTP connectivity using FileZilla's exact settings</p>";

// Use exact FileZilla settings
$ftpTestCredentials = [
    'host' => '147.93.92.53', // IP address from FileZilla
    'username' => 'u706445394.ticketsadmin', // Exact username from FileZilla  
    'password' => '2O|+=18K$[hHfK=s'
];

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>FTP Credential</th><th>Status</th><th>Value</th></tr>";

foreach ($ftpTestCredentials as $key => $value) {
    $isSet = $value !== '' && !in_array($value, ['your-ftp-host.com', 'your-username', 'your-password']);
    echo "<tr>";
    echo "<td><strong>" . strtoupper($key) . "</strong></td>";
    
    if ($isSet) {
        echo "<td style='color: green;'>✅ SET</td>";
        if ($key === 'password') {
            echo "<td>" . str_repeat('*', strlen($value)) . "</td>";
        } else {
            echo "<td>$value</td>";
        }
    } else {
        echo "<td style='color: red;'>❌ MISSING</td>";
        echo "<td>NOT SET OR DEFAULT</td>";
    }
    echo "</tr>";
}
echo "</table>";

// Test FTP connectivity
if ($ftpTestCredentials['password'] !== 'your-password') {
    echo "<h4>🔗 Testing FTP Connection (FileZilla Settings):</h4>";
    echo "<p>Using: <strong>147.93.92.53</strong> (IP address) and <strong>u706445394.ticketsadmin</strong></p>";
    
    // Test standard FTP (port 21)
    echo "<p><strong>Testing FTP (port 21):</strong></p>";
    try {
        $ftpConn = @ftp_connect($ftpTestCredentials['host'], 21, 10);
        if ($ftpConn) {
            $loginResult = @ftp_login($ftpConn, $ftpTestCredentials['username'], $ftpTestCredentials['password']);
            if ($loginResult) {
                echo "<p style='color: green;'>✅ FTP connection successful on port 21</p>";
                $pwd = @ftp_pwd($ftpConn);
                echo "<p>Current directory: <code>$pwd</code></p>";
                @ftp_close($ftpConn);
            } else {
                echo "<p style='color: red;'>❌ FTP login failed (wrong credentials?)</p>";
                @ftp_close($ftpConn);
            }
        } else {
            echo "<p style='color: red;'>❌ FTP connection failed on port 21</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ FTP test error: " . $e->getMessage() . "</p>";
    }
    
    // Test FTPS (port 21 with SSL) - This matches FileZilla's "explicit FTP over TLS"
    echo "<p><strong>Testing FTPS (port 21 with SSL) - FileZilla Mode:</strong></p>";
    try {
        $ftpsConn = @ftp_ssl_connect($ftpTestCredentials['host'], 21, 10);
        if ($ftpsConn) {
            $loginResult = @ftp_login($ftpsConn, $ftpTestCredentials['username'], $ftpTestCredentials['password']);
            if ($loginResult) {
                echo "<p style='color: green;'>✅ FTPS connection successful on port 21 (FileZilla mode)</p>";
                $pwd = @ftp_pwd($ftpsConn);
                echo "<p>Current directory: <code>$pwd</code></p>";
                @ftp_close($ftpsConn);
            } else {
                echo "<p style='color: red;'>❌ FTPS login failed</p>";
                @ftp_close($ftpsConn);
            }
        } else {
            echo "<p style='color: red;'>❌ FTPS connection failed on port 21</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ FTPS test error: " . $e->getMessage() . "</p>";
    }
    
    // Test SFTP (port 22) - requires SSH2 extension
    echo "<p><strong>Testing SFTP (port 22):</strong></p>";
    if (function_exists('ssh2_connect')) {
        try {
            $sftpConn = @ssh2_connect($ftpTestCredentials['host'], 22);
            if ($sftpConn) {
                $authResult = @ssh2_auth_password($sftpConn, $ftpTestCredentials['username'], $ftpTestCredentials['password']);
                if ($authResult) {
                    echo "<p style='color: green;'>✅ SFTP connection successful on port 22</p>";
                    $sftp = @ssh2_sftp($sftpConn);
                    if ($sftp) {
                        echo "<p>SFTP subsystem initialized successfully</p>";
                    }
                } else {
                    echo "<p style='color: red;'>❌ SFTP authentication failed</p>";
                }
            } else {
                echo "<p style='color: red;'>❌ SFTP connection failed on port 22</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ SFTP test error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ SSH2 extension not available - cannot test SFTP</p>";
        echo "<p><small>Install php-ssh2 to test SFTP connections</small></p>";
    }
    
    echo "<h4>📋 GitHub Actions Recommendations:</h4>";
    echo "<ul>";
    echo "<li>✅ <strong>FTP working:</strong> Use standard FTP deployment</li>";
    echo "<li>✅ <strong>FTPS working:</strong> Use FTPS deployment (more secure)</li>";
    echo "<li>✅ <strong>SFTP working:</strong> Use SFTP deployment (most reliable)</li>";
    echo "<li>❌ <strong>All failed:</strong> Check firewall/IP restrictions</li>";
    echo "</ul>";
    
} else {
    echo "<p style='color: orange;'>⚠️ FTP credentials not configured - add them to environment variables to test</p>";
    echo "<pre>";
    echo "Add these to server .env file to test:\n";
    echo "FTP_HOST_TICKETSADMIN=your-ftp-host.com\n";
    echo "FTP_USERNAME_TICKETSADMIN=your-username\n"; 
    echo "FTP_PASSWORD_TICKETSADMIN=your-password\n";
    echo "</pre>";
}

echo "<h3>📊 Test Summary:</h3>";
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

echo "<li>🕒 Test completed at: " . date('Y-m-d H:i:s') . "</li>";
echo "<li>💾 PHP Memory Usage: " . round(memory_get_usage() / 1024 / 1024, 2) . " MB</li>";
echo "<li>⚡ Execution Time: " . round(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 2) . " seconds</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>🚀 Kuantan188 Event Ticketing Platform</strong> | Deployment Test Suite v2.1</p>";
echo "<p><small>💡 For deployment issues, check GitHub Actions logs and ensure all secrets are properly configured</small></p>";
echo "</div>"; // Close container

?>

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