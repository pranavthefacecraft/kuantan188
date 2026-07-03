<?php
/**
 * Check current mail configuration being used by Laravel
 * Access: https://admin.tfcmockup.com/check-mail-config.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Mail Configuration Check</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .good { color: #28a745; font-weight: bold; }
        .bad { color: #dc3545; font-weight: bold; }
        .warning { color: #ffc107; font-weight: bold; }
        pre { background: white; padding: 15px; border: 1px solid #ddd; }
        table { background: white; border-collapse: collapse; width: 100%; margin: 20px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f8f9fa; }
    </style>
</head>
<body>

<h1>📧 Mail Configuration Check</h1>
<p>Server Time: <?php echo date('Y-m-d H:i:s'); ?></p>
<hr>

<h2>1. Environment Variables (.env file)</h2>
<table>
    <tr>
        <th>Variable</th>
        <th>Value</th>
        <th>Status</th>
    </tr>
    <?php
    $envVars = [
        'MAIL_MAILER',
        'MAIL_HOST',
        'MAIL_PORT',
        'MAIL_USERNAME',
        'MAIL_PASSWORD',
        'MAIL_ENCRYPTION',
        'MAIL_FROM_ADDRESS',
        'MAIL_FROM_NAME',
    ];
    
    foreach ($envVars as $var) {
        $value = env($var);
        $display = $value ? (strlen($var) > 15 && strpos($var, 'PASSWORD') !== false ? str_repeat('*', 8) : $value) : '<span class="bad">NOT SET</span>';
        
        $status = '';
        if ($var === 'MAIL_MAILER') {
            $status = $value === 'smtp' ? '<span class="good">✅ Correct</span>' : '<span class="bad">❌ Should be "smtp"</span>';
        } elseif ($var === 'MAIL_HOST') {
            $status = $value === 'smtp-relay.brevo.com' ? '<span class="good">✅ Correct</span>' : '<span class="warning">⚠️ Check value</span>';
        } elseif ($var === 'MAIL_PORT') {
            $status = $value == 587 ? '<span class="good">✅ Correct</span>' : '<span class="warning">⚠️ Should be 587</span>';
        } elseif ($var === 'MAIL_ENCRYPTION') {
            $status = $value === 'tls' ? '<span class="good">✅ Correct</span>' : '<span class="warning">⚠️ Should be "tls"</span>';
        } else {
            $status = $value ? '<span class="good">✅ Set</span>' : '<span class="bad">❌ Not Set</span>';
        }
        
        echo "<tr><td><code>$var</code></td><td>$display</td><td>$status</td></tr>";
    }
    ?>
</table>

<h2>2. Laravel Config Cache</h2>
<table>
    <tr>
        <th>Config Key</th>
        <th>Cached Value</th>
        <th>Status</th>
    </tr>
    <?php
    $configKeys = [
        'mail.default' => 'Default Mailer',
        'mail.mailers.smtp.host' => 'SMTP Host',
        'mail.mailers.smtp.port' => 'SMTP Port',
        'mail.mailers.smtp.username' => 'SMTP Username',
        'mail.mailers.smtp.password' => 'SMTP Password',
        'mail.from.address' => 'From Address',
        'mail.from.name' => 'From Name',
    ];
    
    foreach ($configKeys as $key => $label) {
        $value = config($key);
        $display = $value;
        
        if (strpos($key, 'password') !== false && $value) {
            $display = str_repeat('*', 8);
        }
        
        $status = $value ? '<span class="good">✅</span>' : '<span class="bad">❌ Empty</span>';
        
        if ($key === 'mail.default') {
            $status = $value === 'smtp' ? '<span class="good">✅ Correct (smtp)</span>' : '<span class="bad">❌ Wrong: ' . $value . '</span>';
        }
        
        echo "<tr><td>$label</td><td><code>$display</code></td><td>$status</td></tr>";
    }
    ?>
</table>

<h2>3. Cache Status</h2>
<?php
$configCached = file_exists(__DIR__ . '/../bootstrap/cache/config.php');
?>
<p>
    Config Cache File: <?php echo $configCached ? '<span class="warning">⚠️ EXISTS (cache is active)</span>' : '<span class="good">✅ Does not exist (reading from .env)</span>'; ?>
</p>

<?php if ($configCached): ?>
<div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; margin: 20px 0;">
    <h3>⚠️ Config is CACHED!</h3>
    <p>Laravel is reading from cache, not your .env file.</p>
    <p><strong>To apply .env changes, run:</strong></p>
    <pre>php artisan config:clear</pre>
</div>
<?php endif; ?>

<h2>4. Actions Required</h2>
<?php
$mailer = config('mail.default');
if ($mailer !== 'smtp'):
?>
<div style="background: #f8d7da; border: 1px solid #dc3545; padding: 15px; margin: 20px 0;">
    <h3>❌ Configuration Error</h3>
    <p>Current mailer: <code><?php echo $mailer; ?></code></p>
    <p><strong>Fix:</strong></p>
    <ol>
        <li>Edit your <code>.env</code> file</li>
        <li>Change <code>MAIL_MAILER=<?php echo $mailer; ?></code> to <code>MAIL_MAILER=smtp</code></li>
        <li>Save the file</li>
        <li>Run: <code>php artisan config:clear</code></li>
        <li>Refresh this page to verify</li>
    </ol>
</div>
<?php else: ?>
<div style="background: #d4edda; border: 1px solid #28a745; padding: 15px; margin: 20px 0;">
    <h3>✅ Configuration Looks Good!</h3>
    <p>You can now test sending email:</p>
    <p><a href="test-email.php?email=pranav@thefacecraft.com" style="color: #007bff; font-weight: bold;">→ Send Test Email</a></p>
</div>
<?php endif; ?>

</body>
</html>
