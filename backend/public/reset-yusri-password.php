<?php
/**
 * Force Password Reset for yusri@thefacecraft.com
 * Access via: https://admin.tfcmockup.com/reset-yusri-password.php
 */

// Load Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/html; charset=utf-8');

$newPassword = 'TFC@yusri2026';
$email = 'yusri@thefacecraft.com';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h1 { color: #333; }
        .success { color: #10b981; padding: 15px; background: #d1fae5; border-radius: 4px; margin: 20px 0; }
        .error { color: #ef4444; padding: 15px; background: #fee2e2; border-radius: 4px; margin: 20px 0; }
        .info { background: #e0e7ff; padding: 15px; border-radius: 4px; margin: 20px 0; }
        code { background: #f1f5f9; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .credentials { background: #fef3c7; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #f59e0b; }
        .credentials h3 { margin-top: 0; color: #92400e; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Force Password Reset</h1>
        
        <?php
        try {
            // Find the user
            $user = \App\Models\User::where('email', $email)->first();
            
            if (!$user) {
                echo '<div class="error"><strong>Error:</strong> User not found!</div>';
            } else {
                // Update password using DB query directly
                DB::table('users')
                    ->where('email', $email)
                    ->update([
                        'password' => bcrypt($newPassword),
                        'email_verified_at' => now(),
                        'updated_at' => now()
                    ]);
                
                // Verify the update
                $updatedUser = \App\Models\User::where('email', $email)->first();
                $passwordWorks = \Hash::check($newPassword, $updatedUser->password);
                
                if ($passwordWorks) {
                    echo '<div class="success">';
                    echo '<h2>✓ Password Reset Successful!</h2>';
                    echo '<p>The password has been updated and verified.</p>';
                    echo '</div>';
                    
                    echo '<div class="credentials">';
                    echo '<h3>📋 Login Credentials</h3>';
                    echo '<p><strong>URL:</strong> <a href="https://admin.tfcmockup.com/login" target="_blank">https://admin.tfcmockup.com/login</a></p>';
                    echo '<p><strong>Email:</strong> <code>' . htmlspecialchars($email) . '</code></p>';
                    echo '<p><strong>Password:</strong> <code>' . htmlspecialchars($newPassword) . '</code></p>';
                    echo '</div>';
                    
                    echo '<div class="info">';
                    echo '<h3>User Details:</h3>';
                    echo '<ul>';
                    echo '<li><strong>ID:</strong> ' . $updatedUser->id . '</li>';
                    echo '<li><strong>Name:</strong> ' . htmlspecialchars($updatedUser->name) . '</li>';
                    echo '<li><strong>Email Verified:</strong> Yes</li>';
                    echo '<li><strong>Password Hash:</strong> ' . substr($updatedUser->password, 0, 60) . '...</li>';
                    echo '</ul>';
                    echo '</div>';
                    
                } else {
                    echo '<div class="error">';
                    echo '<strong>Warning:</strong> Password was updated but verification failed. Please check manually.';
                    echo '</div>';
                }
            }
            
        } catch (\Exception $e) {
            echo '<div class="error">';
            echo '<strong>Error:</strong> ' . htmlspecialchars($e->getMessage());
            echo '</div>';
        }
        ?>
        
        <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-radius: 4px;">
            <strong>⚠️ Security Notice:</strong> Delete this file immediately after use!
        </div>
    </div>
</body>
</html>
