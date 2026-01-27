<?php
/**
 * SFTP Connection Test without SSH2 extension
 * Use cURL to test SFTP connectivity
 */

echo "<h2>🔒 SFTP Connection Test (Alternative Method)</h2>";

$ftpHost = $_ENV['FTP_HOST_TICKETSADMIN'] ?? getenv('FTP_HOST_TICKETSADMIN') ?? '';
$ftpUser = $_ENV['FTP_USERNAME_TICKETSADMIN'] ?? getenv('FTP_USERNAME_TICKETSADMIN') ?? '';
$ftpPass = $_ENV['FTP_PASSWORD_TICKETSADMIN'] ?? getenv('FTP_PASSWORD_TICKETSADMIN') ?? '';

if ($ftpHost && $ftpUser && $ftpPass) {
    echo "<h3>Testing SFTP via cURL (Port 22):</h3>";
    
    // Test SFTP connection with cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "sftp://$ftpHost:22/");
    curl_setopt($ch, CURLOPT_USERPWD, "$ftpUser:$ftpPass");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
    curl_setopt($ch, CURLOPT_DIRLISTONLY, true);
    
    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $connectTime = curl_getinfo($ch, CURLINFO_CONNECT_TIME);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($result !== false && empty($error)) {
        echo "<p style='color: green;'>✅ <strong>SFTP connection successful!</strong></p>";
        echo "<p>Connection time: {$connectTime} seconds</p>";
        if ($result) {
            echo "<p>Directory listing available</p>";
            echo "<pre>" . htmlspecialchars(substr($result, 0, 200)) . "...</pre>";
        }
        
        echo "<h3>🎯 Recommended Solution:</h3>";
        echo "<p style='background: #e8f5e8; padding: 10px; border-left: 5px solid green;'>";
        echo "<strong>Use SFTP deployment!</strong><br>";
        echo "Your server supports SFTP on port 22. Update GitHub Actions to use the SFTP workflow.";
        echo "</p>";
        
    } else {
        echo "<p style='color: red;'>❌ <strong>SFTP connection failed</strong></p>";
        if ($error) {
            echo "<p>cURL Error: " . htmlspecialchars($error) . "</p>";
        }
        echo "<p>HTTP Code: $httpCode</p>";
    }
    
    echo "<h3>🔄 Alternative FTP Ports Test:</h3>";
    $alternativePorts = [990, 989, 2121, 21000];
    
    foreach ($alternativePorts as $port) {
        echo "<p><strong>Testing FTP on port $port:</strong></p>";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "ftp://$ftpHost:$port/");
        curl_setopt($ch, CURLOPT_USERPWD, "$ftpUser:$ftpPass");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_DIRLISTONLY, true);
        
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($result !== false && empty($error)) {
            echo "<p style='color: green;'>✅ FTP working on port $port</p>";
        } else {
            echo "<p style='color: red;'>❌ FTP failed on port $port</p>";
        }
    }
    
    echo "<h3>📋 Deployment Recommendations:</h3>";
    echo "<ul>";
    echo "<li><strong>If SFTP works:</strong> Use <code>deploy-backend-sftp.yml</code> workflow</li>";
    echo "<li><strong>If alternative FTP port works:</strong> Update port in deployment config</li>";
    echo "<li><strong>If all fail:</strong> Contact hosting provider about server-to-server FTP restrictions</li>";
    echo "</ul>";
    
} else {
    echo "<p style='color: red;'>❌ FTP credentials not found in environment variables</p>";
}

echo "<h3>🛠️ Manual Deployment Alternative:</h3>";
echo "<p>If automated deployment continues to fail, you can:</p>";
echo "<ol>";
echo "<li>Use FileZilla to manually upload files</li>";
echo "<li>Set up a local deployment script</li>";
echo "<li>Use Git hooks on the server</li>";
echo "</ol>";

echo "<p><a href='quick-test.php'>← Back to Quick Test</a></p>";
?>