<?php
// Emergency cache clearing script for production
require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once 'bootstrap/app.php';

try {
    // Clear various caches
    $app->make('Illuminate\Contracts\Console\Kernel')->call('config:clear');
    $app->make('Illuminate\Contracts\Console\Kernel')->call('cache:clear');
    $app->make('Illuminate\Contracts\Console\Kernel')->call('view:clear');
    $app->make('Illuminate\Contracts\Console\Kernel')->call('route:clear');
    
    echo "All caches cleared successfully!\n";
} catch (Exception $e) {
    echo "Error clearing caches: " . $e->getMessage() . "\n";
}