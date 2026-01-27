<?php
/**
 * Direct Database Connection Test
 * Test specific database credentials without relying on Laravel .env
 */

echo "<h2>🔍 Direct Database Connection Test</h2>";

// Test credentials from screenshot
$testCredentials = [
    'host' => 'srv1317.hstgr.io',
    'port' => 3306,
    'database' => 'u706445394_kuantanticket', 
    'username' => 'u706445394_kuantanticket',
    'password' => '10xuklT/SHm7'
];

echo "<h3>Testing Connection:</h3>";
echo "<p><strong>Host:</strong> {$testCredentials['host']}:{$testCredentials['port']}</p>";
echo "<p><strong>Database:</strong> {$testCredentials['database']}</p>";
echo "<p><strong>Username:</strong> {$testCredentials['username']}</p>";
echo "<p><strong>Password:</strong> " . str_repeat('*', strlen($testCredentials['password'])) . "</p>";

try {
    $dsn = "mysql:host={$testCredentials['host']};port={$testCredentials['port']};dbname={$testCredentials['database']}";
    
    $pdo = new PDO($dsn, $testCredentials['username'], $testCredentials['password'], [
        PDO::ATTR_TIMEOUT => 10,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "<p style='color: green; font-size: 18px;'>✅ <strong>Database connection SUCCESSFUL!</strong></p>";
    
    // Test basic queries
    echo "<h3>Database Information:</h3>";
    
    $version = $pdo->query("SELECT VERSION() as version")->fetch();
    echo "<p><strong>MySQL Version:</strong> {$version['version']}</p>";
    
    $charset = $pdo->query("SELECT @@character_set_database as charset")->fetch();
    echo "<p><strong>Database Charset:</strong> {$charset['charset']}</p>";
    
    // Test tables
    echo "<h3>Tables in Database:</h3>";
    $tables = $pdo->query("SHOW TABLES")->fetchAll();
    
    if (count($tables) > 0) {
        echo "<ul>";
        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            
            // Get row count for each table
            try {
                $count = $pdo->query("SELECT COUNT(*) as count FROM `$tableName`")->fetch();
                echo "<li><strong>$tableName</strong>: {$count['count']} rows</li>";
            } catch (Exception $e) {
                echo "<li><strong>$tableName</strong>: (could not count rows)</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ No tables found in database</p>";
    }
    
    echo "<h3>✅ Connection Test Results:</h3>";
    echo "<p style='color: green;'><strong>SUCCESS!</strong> Your database credentials are working perfectly.</p>";
    echo "<p><strong>Issue:</strong> The credentials are not being loaded from your server's .env file.</p>";
    
    echo "<h3>🔧 Fix Required:</h3>";
    echo "<p>Add these exact lines to your server's <code>.env</code> file:</p>";
    echo "<pre style='background: #f5f5f5; padding: 10px; border-left: 3px solid green;'>";
    echo "DB_CONNECTION=mysql\n";
    echo "DB_HOST=srv1317.hstgr.io\n";
    echo "DB_PORT=3306\n";
    echo "DB_DATABASE=u706445394_kuantanticket\n";
    echo "DB_USERNAME=u706445394_kuantanticket\n";
    echo "DB_PASSWORD=10xuklT/SHm7\n";
    echo "</pre>";
    
    echo "<h3>📝 After updating .env:</h3>";
    echo "<ol>";
    echo "<li>Run: <code>php artisan config:clear</code></li>";
    echo "<li>Run: <code>php artisan config:cache</code></li>";
    echo "<li>Test again at: <a href='quick-test.php'>quick-test.php</a></li>";
    echo "</ol>";
    
} catch (PDOException $e) {
    echo "<p style='color: red; font-size: 18px;'>❌ <strong>Database connection FAILED!</strong></p>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<p>🔍 <strong>Diagnosis:</strong> Username/password incorrect</p>";
    } elseif (strpos($e->getMessage(), 'Unknown database') !== false) {
        echo "<p>🔍 <strong>Diagnosis:</strong> Database name incorrect</p>";
    } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
        echo "<p>🔍 <strong>Diagnosis:</strong> Host/port incorrect or server down</p>";
    } else {
        echo "<p>🔍 <strong>Diagnosis:</strong> General connection error</p>";
    }
}

echo "<h3>🔗 Quick Links:</h3>";
echo "<ul>";
echo "<li><a href='quick-test.php'>Back to Quick Test</a></li>";
echo "<li><a href='check-billplz-config.php'>Check Billplz Config</a></li>";
echo "</ul>";

$executionTime = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];
echo "<p><small>Execution time: " . round($executionTime, 2) . " seconds</small></p>";
?>