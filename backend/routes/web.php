<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Admin Dashboard Routes (protected by auth middleware)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/events', [AdminDashboardController::class, 'events'])->name('events');
    Route::post('/events', [AdminDashboardController::class, 'storeEvent'])->name('events.store');
    Route::get('/events/{event}/edit', [AdminDashboardController::class, 'editEvent'])->name('events.edit');
    Route::put('/events/{event}', [AdminDashboardController::class, 'updateEvent'])->name('events.update');
    Route::post('/events/{event}/toggle-status', [AdminDashboardController::class, 'toggleEventStatus'])->name('events.toggle-status');
    Route::get('/bookings', [AdminDashboardController::class, 'bookings'])->name('bookings');
    Route::get('/tickets', [AdminDashboardController::class, 'tickets'])->name('tickets');
    Route::post('/tickets', [AdminDashboardController::class, 'storeTicket'])->name('tickets.store');
    Route::get('/tickets/{ticket}/edit', [AdminDashboardController::class, 'editTicket'])->name('tickets.edit');
    Route::put('/tickets/{ticket}', [AdminDashboardController::class, 'updateTicket'])->name('tickets.update');
    Route::delete('/tickets/{ticket}', [AdminDashboardController::class, 'destroyTicket'])->name('tickets.destroy');
    Route::post('/tickets/bulk-delete', [AdminDashboardController::class, 'bulkDeleteTickets'])->name('tickets.bulk-delete');
    Route::get('/countries', [AdminDashboardController::class, 'countries'])->name('countries');
});

// Debug route to check CSRF token and session
Route::get('/debug/csrf', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'app_env' => config('app.env'),
        'session_driver' => config('session.driver'),
        'session_domain' => config('session.domain'),
        'session_secure' => config('session.secure'),
        'session_same_site' => config('session.same_site'),
        'session_http_only' => config('session.http_only'),
        'session_path' => config('session.path'),
        'app_url' => config('app.url'),
        'session_started' => session()->isStarted(),
        'session_data' => session()->all(),
    ]);
});

// Debug route to clear all sessions
Route::get('/debug/clear-session', function () {
    session()->flush();
    session()->regenerate();
    return response()->json(['message' => 'Session cleared and regenerated']);
});

// Debug route to check users and authentication
Route::get('/debug/users', function () {
    $users = \App\Models\User::all(['id', 'name', 'email', 'email_verified_at']);
    $authUser = auth()->user();
    
    return response()->json([
        'users_count' => $users->count(),
        'users' => $users->toArray(),
        'authenticated_user' => $authUser ? $authUser->toArray() : null,
        'auth_check' => auth()->check(),
        'auth_guard' => config('auth.defaults.guard'),
        'auth_provider' => config('auth.providers.users.driver'),
    ]);
});

// Debug route to test login manually
Route::post('/debug/test-login', function () {
    $credentials = request()->only('email', 'password');
    
    $attempt = auth()->attempt($credentials);
    
    return response()->json([
        'credentials_provided' => $credentials,
        'login_attempt_result' => $attempt,
        'auth_after_attempt' => auth()->check(),
        'user_after_attempt' => auth()->user() ? auth()->user()->toArray() : null,
    ]);
});

// Debug route to create admin user if it doesn't exist
Route::get('/debug/create-admin', function () {
    $existingUser = \App\Models\User::where('email', 'admin@kuantan188.com')->first();
    
    if ($existingUser) {
        return response()->json([
            'message' => 'Admin user already exists',
            'user' => $existingUser->toArray()
        ]);
    }
    
    $user = \App\Models\User::create([
        'name' => 'Admin User',
        'email' => 'admin@kuantan188.com',
        'password' => bcrypt('password123'),
        'email_verified_at' => now(),
    ]);
    
    return response()->json([
        'message' => 'Admin user created successfully',
        'user' => $user->toArray(),
        'login_credentials' => [
            'email' => 'admin@kuantan188.com',
            'password' => 'password123'
        ]
    ]);
});

// Deployment route to clear all caches (use carefully in production)
Route::get('/deploy/clear-cache', function () {
    $results = [];
    
    try {
        // Clear application cache
        \Artisan::call('cache:clear');
        $results['cache_clear'] = 'Success';
        
        // Clear configuration cache
        \Artisan::call('config:clear');
        $results['config_clear'] = 'Success';
        
        // Clear route cache
        \Artisan::call('route:clear');
        $results['route_clear'] = 'Success';
        
        // Clear view cache
        \Artisan::call('view:clear');
        $results['view_clear'] = 'Success';
        
        // Clear compiled classes
        \Artisan::call('clear-compiled');
        $results['clear_compiled'] = 'Success';
        
        // Optimize for production
        \Artisan::call('config:cache');
        $results['config_cache'] = 'Success';
        
        \Artisan::call('route:cache');
        $results['route_cache'] = 'Success';
        
    } catch (\Exception $e) {
        $results['error'] = $e->getMessage();
    }
    
    return response()->json([
        'message' => 'Cache clearing completed',
        'results' => $results,
        'timestamp' => now()->toDateTimeString()
    ]);
});
