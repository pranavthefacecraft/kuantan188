<?php
/**
 * User Database Checker
 * Access via: https://admin.tfcmockup.com/check-users.php
 */

// Load Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>User Database Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #6366f1; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #6366f1; color: white; }
        tr:hover { background: #f9f9f9; }
        .success { color: #10b981; font-weight: bold; }
        .error { color: #ef4444; font-weight: bold; }
        .info { background: #e0e7ff; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .password-hash { font-family: monospace; font-size: 11px; word-break: break-all; max-width: 300px; }
        .test-section { margin-top: 30px; padding: 20px; background: #f0f9ff; border-radius: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 User Database Check</h1>
        
        <div class="info">
            <strong>Database Connection:</strong> 
            <?php 
            try {
                DB::connection()->getPdo();
                echo '<span class="success">✓ Connected</span>';
            } catch (\Exception $e) {
                echo '<span class="error">✗ Failed: ' . $e->getMessage() . '</span>';
            }
            ?>
        </div>

        <h2>📋 All Users in Database</h2>
        <?php
        try {
            $users = \App\Models\User::all();
            
            if ($users->count() > 0) {
                echo '<p class="success">Found ' . $users->count() . ' user(s)</p>';
                echo '<table>';
                echo '<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Email Verified</th><th>Created At</th><th>Password Hash (first 50 chars)</th></tr></thead>';
                echo '<tbody>';
                
                foreach ($users as $user) {
                    echo '<tr>';
                    echo '<td>' . $user->id . '</td>';
                    echo '<td>' . htmlspecialchars($user->name) . '</td>';
                    echo '<td><strong>' . htmlspecialchars($user->email) . '</strong></td>';
                    echo '<td>' . ($user->email_verified_at ? '<span class="success">✓ Verified</span>' : '<span class="error">✗ Not Verified</span>') . '</td>';
                    echo '<td>' . $user->created_at->format('Y-m-d H:i:s') . '</td>';
                    echo '<td class="password-hash">' . substr($user->password, 0, 50) . '...</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<p class="error">No users found in database!</p>';
            }
        } catch (\Exception $e) {
            echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
        }
        ?>

        <div class="test-section">
            <h2>🔐 Password Hash Test for yusri@thefacecraft.com</h2>
            <?php
            try {
                $testUser = \App\Models\User::where('email', 'yusri@thefacecraft.com')->first();
                
                if ($testUser) {
                    echo '<p class="success">✓ User found!</p>';
                    echo '<ul>';
                    echo '<li><strong>User ID:</strong> ' . $testUser->id . '</li>';
                    echo '<li><strong>Name:</strong> ' . htmlspecialchars($testUser->name) . '</li>';
                    echo '<li><strong>Email:</strong> ' . htmlspecialchars($testUser->email) . '</li>';
                    echo '<li><strong>Full Password Hash:</strong> <code>' . $testUser->password . '</code></li>';
                    echo '<li><strong>Hash Length:</strong> ' . strlen($testUser->password) . ' characters</li>';
                    echo '<li><strong>Email Verified:</strong> ' . ($testUser->email_verified_at ? 'Yes' : 'No') . '</li>';
                    echo '</ul>';
                    
                    // Test password verification
                    echo '<h3>Password Test Results:</h3>';
                    $testPasswords = ['Admin@123', 'test123', 'Ysr@TFC2026!#SecureAdmin', 'password123'];
                    
                    foreach ($testPasswords as $testPassword) {
                        $matches = \Hash::check($testPassword, $testUser->password);
                        $status = $matches ? '<span class="success">✓ MATCHES</span>' : '<span class="error">✗ No match</span>';
                        echo '<p>Testing password "<strong>' . htmlspecialchars($testPassword) . '</strong>": ' . $status . '</p>';
                    }
                } else {
                    echo '<p class="error">✗ User not found!</p>';
                }
            } catch (\Exception $e) {
                echo '<p class="error">Error: ' . $e->getMessage() . '</p>';
            }
            ?>
        </div>

        <div style="margin-top: 20px; padding: 10px; background: #fff3cd; border-radius: 4px;">
            <strong>⚠️ Security Notice:</strong> Delete this file after testing!
        </div>
    </div>
</body>
</html>
