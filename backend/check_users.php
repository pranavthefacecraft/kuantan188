<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Users in database:\n";
echo "==================\n";

try {
    $users = App\Models\User::all();
    
    if ($users->count() > 0) {
        foreach ($users as $user) {
            echo "ID: {$user->id}\n";
            echo "Name: {$user->name}\n";
            echo "Email: {$user->email}\n";
            echo "Created: {$user->created_at}\n";
            echo "---\n";
        }
    } else {
        echo "No users found in database.\n";
    }
    
    // Also check for any admin-specific settings
    echo "\nChecking for admin email in settings...\n";
    $adminEmail = env('ADMIN_EMAIL', 'Not set');
    echo "ADMIN_EMAIL from .env: {$adminEmail}\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}