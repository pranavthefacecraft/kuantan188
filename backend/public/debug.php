<?php
echo "=== SERVER DEBUG INFO ===\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Parent directory: " . dirname(__DIR__) . "\n";
echo "Document root: " . (isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : 'N/A') . "\n";
echo "Script filename: " . (isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : 'N/A') . "\n";

echo "\n=== CURRENT DIRECTORY CONTENTS ===\n";
foreach (scandir(__DIR__) as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file" . (is_dir(__DIR__ . '/' . $file) ? '/' : '') . "\n";
    }
}

echo "\n=== PARENT DIRECTORY CONTENTS ===\n";
foreach (scandir(dirname(__DIR__)) as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file" . (is_dir(dirname(__DIR__) . '/' . $file) ? '/' : '') . "\n";
    }
}

echo "\n=== LARAVEL FILES CHECK (current) ===\n";
echo "composer.json: " . (file_exists(__DIR__ . '/composer.json') ? 'YES' : 'NO') . "\n";
echo "vendor/: " . (is_dir(__DIR__ . '/vendor') ? 'YES' : 'NO') . "\n";
echo "bootstrap/: " . (is_dir(__DIR__ . '/bootstrap') ? 'YES' : 'NO') . "\n";

echo "\n=== LARAVEL FILES CHECK (parent) ===\n";
echo "composer.json: " . (file_exists(dirname(__DIR__) . '/composer.json') ? 'YES' : 'NO') . "\n";
echo "vendor/: " . (is_dir(dirname(__DIR__) . '/vendor') ? 'YES' : 'NO') . "\n";
echo "bootstrap/: " . (is_dir(dirname(__DIR__) . '/bootstrap') ? 'YES' : 'NO') . "\n";
echo "storage/: " . (is_dir(dirname(__DIR__) . '/storage') ? 'YES' : 'NO') . "\n";

echo "\n=== END DEBUG INFO ===\n";
?>