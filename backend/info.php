<?php
echo "Current directory (__DIR__): " . __DIR__ . "\n";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Script filename: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "\nDirectory contents:\n";
echo "Files in current directory:\n";
foreach (scandir(__DIR__) as $file) {
    if ($file != '.' && $file != '..') {
        echo "- $file\n";
    }
}
echo "\nLaravel folders check:\n";
echo "vendor/ exists: " . (is_dir(__DIR__ . '/vendor') ? 'YES' : 'NO') . "\n";
echo "storage/ exists: " . (is_dir(__DIR__ . '/storage') ? 'YES' : 'NO') . "\n";
echo "bootstrap/ exists: " . (is_dir(__DIR__ . '/bootstrap') ? 'YES' : 'NO') . "\n";
echo "public/ exists: " . (is_dir(__DIR__ . '/public') ? 'YES' : 'NO') . "\n";
?>