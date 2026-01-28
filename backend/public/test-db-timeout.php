<?php
// Test database connection with timeout settings
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M');

echo "<h2>Database Connection Test with Timeout Settings</h2>";
echo "<p>Testing connection to remote database...</p>";

try {
    // Load Laravel environment
    require_once '../vendor/autoload.php';
    
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
    
    $host = $_ENV['DB_HOST'];
    $database = $_ENV['DB_DATABASE'];
    $username = $_ENV['DB_USERNAME'];
    $password = $_ENV['DB_PASSWORD'];
    
    echo "<p>Connecting to: {$host}:{$database}</p>";
    
    $start_time = microtime(true);
    
    // Test connection with timeout
    $pdo = new PDO(
        "mysql:host={$host};port=3306;dbname={$database}",
        $username,
        $password,
        [
            PDO::ATTR_TIMEOUT => 30,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET SESSION wait_timeout=300"
        ]
    );
    
    $end_time = microtime(true);
    $connection_time = round(($end_time - $start_time) * 1000, 2);
    
    echo "<p style='color: green;'>✅ Database connected successfully!</p>";
    echo "<p>Connection time: {$connection_time}ms</p>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '{$database}'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Tables found: {$result['table_count']}</p>";
    
    // Test timeout settings
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'wait_timeout'");
    $timeout_info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>Current wait_timeout: {$timeout_info['Value']} seconds</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Error code: " . $e->getCode() . "</p>";
}

echo "<hr>";
echo "<h3>Current PHP Settings:</h3>";
echo "<p>Max execution time: " . ini_get('max_execution_time') . " seconds</p>";
echo "<p>Memory limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max input time: " . ini_get('max_input_time') . " seconds</p>";
?>