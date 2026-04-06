<?php
/**
 * Temporary script to fix mail configuration on production.
 * DELETE THIS FILE AFTER USE.
 */

// Security: only allow with correct token
$token = $_GET['token'] ?? '';
if ($token !== 'fix-mail-2026-apr06') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$envPath = dirname(__DIR__) . '/.env';

if (!file_exists($envPath)) {
    echo json_encode(['error' => '.env file not found', 'path' => $envPath]);
    exit;
}

$env = file_get_contents($envPath);

// Show current mail config
preg_match_all('/^MAIL_.+=.*/m', $env, $matches);
echo "<h3>Current Mail Config:</h3><pre>";
foreach ($matches[0] as $line) {
    echo htmlspecialchars($line) . "\n";
}
echo "</pre>";

if (isset($_GET['fix']) && $_GET['fix'] === 'yes') {
    // Fix mail settings
    $replacements = [
        '/^MAIL_MAILER=.*/m' => 'MAIL_MAILER=smtp',
        '/^MAIL_SCHEME=.*/m' => 'MAIL_SCHEME=null',
        '/^MAIL_HOST=.*/m' => 'MAIL_HOST=smtp.gmail.com',
        '/^MAIL_PORT=.*/m' => 'MAIL_PORT=587',
        '/^MAIL_USERNAME=.*/m' => 'MAIL_USERNAME=pranav@thefacecraft.com',
        '/^MAIL_PASSWORD=.*/m' => 'MAIL_PASSWORD="tggm fawo eftz kbua"',
        '/^MAIL_FROM_ADDRESS=.*/m' => 'MAIL_FROM_ADDRESS="pranav@thefacecraft.com"',
        '/^MAIL_FROM_NAME=.*/m' => 'MAIL_FROM_NAME="Kuantan 188"',
    ];

    foreach ($replacements as $pattern => $replacement) {
        if (preg_match($pattern, $env)) {
            $env = preg_replace($pattern, $replacement, $env);
        }
    }

    file_put_contents($envPath, $env);

    // Clear config cache
    $artisan = dirname(__DIR__) . '/artisan';
    if (file_exists($artisan)) {
        exec('cd ' . dirname(__DIR__) . ' && php artisan config:clear 2>&1', $output);
        echo "<h3>Config cache cleared:</h3><pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
    }

    // Show updated config
    $env = file_get_contents($envPath);
    preg_match_all('/^MAIL_.+=.*/m', $env, $matches);
    echo "<h3>Updated Mail Config:</h3><pre>";
    foreach ($matches[0] as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    echo "</pre>";
    echo "<p style='color:green;font-weight:bold;'>Mail config updated! Now delete this file from the server.</p>";
} else {
    echo "<p><a href='?token=" . htmlspecialchars($token) . "&fix=yes'>Click here to fix mail config to use smtp.gmail.com</a></p>";
}
