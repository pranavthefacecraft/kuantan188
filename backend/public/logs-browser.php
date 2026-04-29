<?php
// Secure browser log viewer for Laravel log file.

header('Content-Type: text/html; charset=UTF-8');

function parseEnvValue(string $line): ?array
{
    $line = trim($line);
    if ($line === '' || $line[0] === '#') {
        return null;
    }

    $parts = explode('=', $line, 2);
    if (count($parts) !== 2) {
        return null;
    }

    $key = trim($parts[0]);
    $value = trim($parts[1]);

    if ($value !== '' && ($value[0] === '"' || $value[0] === '\'')) {
        $quote = $value[0];
        if (substr($value, -1) === $quote) {
            $value = substr($value, 1, -1);
        }
    }

    return [$key, $value];
}

function readEnvToken(string $envPath): string
{
    if (!file_exists($envPath)) {
        return '';
    }

    $lines = @file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return '';
    }

    foreach ($lines as $line) {
        $parsed = parseEnvValue($line);
        if ($parsed === null) {
            continue;
        }

        [$key, $value] = $parsed;
        if ($key === 'LOG_VIEWER_TOKEN') {
            return $value;
        }
    }

    return '';
}

$rootPath = dirname(__DIR__);
$envPath = $rootPath . DIRECTORY_SEPARATOR . '.env';
$logPath = $rootPath . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'laravel.log';
$expectedToken = readEnvToken($envPath);
$providedToken = isset($_GET['token']) ? trim((string) $_GET['token']) : '';

if ($expectedToken === '') {
    http_response_code(503);
    echo '<h3>Log viewer disabled</h3>';
    echo '<p>Set LOG_VIEWER_TOKEN in .env to enable this page.</p>';
    exit;
}

if (!hash_equals($expectedToken, $providedToken)) {
    http_response_code(403);
    echo '<h3>Forbidden</h3>';
    echo '<p>Invalid or missing token.</p>';
    exit;
}

if (!file_exists($logPath)) {
    http_response_code(404);
    echo '<h3>Log file not found</h3>';
    exit;
}

$linesParam = isset($_GET['lines']) ? (int) $_GET['lines'] : 200;
$linesParam = max(20, min($linesParam, 1000));

$allLines = @file($logPath, FILE_IGNORE_NEW_LINES);
if ($allLines === false) {
    http_response_code(500);
    echo '<h3>Unable to read log file</h3>';
    exit;
}

$tail = array_slice($allLines, -$linesParam);

function esc(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$self = strtok($_SERVER['REQUEST_URI'] ?? 'logs-browser.php', '?');
$refreshUrl = $self . '?token=' . rawurlencode($providedToken) . '&lines=' . $linesParam;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel Log Viewer</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 16px; background: #111827; color: #f3f4f6; }
        .bar { display: flex; gap: 10px; align-items: center; margin-bottom: 12px; flex-wrap: wrap; }
        .meta { color: #9ca3af; font-size: 13px; }
        .btn { background: #2563eb; color: #fff; border: 0; border-radius: 6px; padding: 8px 12px; text-decoration: none; }
        pre { background: #0b1220; border: 1px solid #374151; border-radius: 8px; padding: 12px; overflow: auto; max-height: 78vh; white-space: pre-wrap; }
        .line { margin: 0; padding: 2px 0; border-bottom: 1px solid #1f2937; }
        .err { color: #fca5a5; }
        .warn { color: #fde68a; }
        .info { color: #93c5fd; }
    </style>
</head>
<body>
    <h2>Laravel Log Viewer</h2>
    <div class="bar">
        <span class="meta">File: <?php echo esc($logPath); ?></span>
        <span class="meta">Showing last <?php echo (int) $linesParam; ?> lines</span>
        <a class="btn" href="<?php echo esc($refreshUrl); ?>">Refresh</a>
    </div>
    <pre>
<?php foreach ($tail as $line): ?>
<?php
    $class = 'info';
    if (stripos($line, 'error') !== false || stripos($line, 'exception') !== false) {
        $class = 'err';
    } elseif (stripos($line, 'warning') !== false) {
        $class = 'warn';
    }
?>
<div class="line <?php echo esc($class); ?>"><?php echo esc($line); ?></div>
<?php endforeach; ?>
    </pre>
</body>
</html>
