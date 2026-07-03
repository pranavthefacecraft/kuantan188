<?php
/**
 * Check admin users in database
 */

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

header('Content-Type: application/json');

try {
    $users = \App\Models\User::all(['id', 'name', 'email', 'email_verified_at', 'created_at']);
    
    echo json_encode([
        'status' => 'success',
        'total_users' => $users->count(),
        'users' => $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'verified' => $user->email_verified_at ? 'Yes' : 'No',
                'created_at' => $user->created_at->format('Y-m-d H:i:s'),
            ];
        }),
    ], JSON_PRETTY_PRINT);
    
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ], JSON_PRETTY_PRINT);
}
