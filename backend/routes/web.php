<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    \Log::info('Root route accessed', [
        'authenticated' => Auth::check(),
        'session_id' => session()->getId(),
        'ip' => request()->ip(),
    ]);
    
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

// Log any request to login routes
Route::middleware(['web'])->group(function () {
    Auth::routes();
});

// Test route to verify requests are reaching Laravel
Route::get('/test-connection', function () {
    \Log::info('Test connection route accessed', [
        'timestamp' => now()->toDateTimeString(),
        'session_id' => session()->getId(),
        'ip' => request()->ip(),
    ]);
    
    return response()->json([
        'status' => 'Laravel is working',
        'timestamp' => now()->toDateTimeString(),
        'session_id' => session()->getId(),
    ]);
});

// Debug authentication state after login
Route::get('/debug/auth-state', function () {
    \Log::info('Auth state check', [
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'user' => Auth::user(),
        'session_id' => session()->getId(),
        'session_started' => session()->isStarted(),
        'session_data' => session()->all(),
        'guards' => array_keys(config('auth.guards')),
        'default_guard' => config('auth.defaults.guard'),
    ]);
    
    return response()->json([
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'user' => Auth::user(),
        'session_id' => session()->getId(),
        'session_data' => session()->all(),
    ]);
});

// Test admin access without auth middleware
Route::get('/debug/admin-bypass', function () {
    \Log::info('Admin bypass test', [
        'authenticated' => Auth::check(),
        'user_id' => Auth::id(),
        'session_id' => session()->getId(),
    ]);
    
    if (Auth::check()) {
        return "✅ You are authenticated as user " . Auth::id() . " - " . Auth::user()->email;
    } else {
        return "❌ You are not authenticated";
    }
});

// Test session persistence
Route::get('/debug/session-test', function () {
    $counter = session('test_counter', 0) + 1;
    session(['test_counter' => $counter]);
    session()->save(); // Force save
    
    \Log::info('Session test', [
        'counter' => $counter,
        'session_id' => session()->getId(),
        'session_driver' => config('session.driver'),
        'session_path' => session_save_path(),
    ]);
    
    return response()->json([
        'counter' => $counter,
        'session_id' => session()->getId(),
        'message' => 'Refresh this page to test if sessions persist'
    ]);
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Admin Dashboard Routes (protected by auth middleware)
Route::prefix('admin')->name('admin.')->middleware(['log.auth', 'auth'])->group(function () {
    Route::get('/', function () {
        \Log::info('Admin dashboard accessed', [
            'authenticated' => Auth::check(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
            'ip' => request()->ip(),
            'session_data' => session()->all(),
        ]);
        
        return app(AdminDashboardController::class)->index();
    })->name('dashboard');
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

// Simple test route
Route::get('/test-route', function () {
    return response()->json([
        'message' => 'New routes are working!',
        'timestamp' => now()->toDateTimeString(),
        'routes_cached' => app()->routesAreCached(),
    ]);
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

// Debug route to test login manually with detailed logging
Route::post('/debug/test-login', function () {
    $credentials = request()->only('email', 'password');
    
    \Log::info('Debug login attempt started', [
        'email' => $credentials['email'] ?? 'not provided',
        'password_provided' => isset($credentials['password']) ? 'yes' : 'no',
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token(),
    ]);
    
    // Check if user exists
    $user = \App\Models\User::where('email', $credentials['email'] ?? '')->first();
    \Log::info('User lookup result', [
        'user_found' => $user ? 'yes' : 'no',
        'user_id' => $user->id ?? null,
        'user_email_verified' => $user && $user->email_verified_at ? 'yes' : 'no',
    ]);
    
    // Attempt login
    $attempt = auth()->attempt($credentials);
    \Log::info('Login attempt result', [
        'attempt_successful' => $attempt ? 'yes' : 'no',
        'auth_check_after' => auth()->check() ? 'yes' : 'no',
        'authenticated_user_id' => auth()->id(),
    ]);
    
    return response()->json([
        'credentials_provided' => $credentials,
        'login_attempt_result' => $attempt,
        'auth_after_attempt' => auth()->check(),
        'user_after_attempt' => auth()->user() ? auth()->user()->toArray() : null,
        'user_exists_in_db' => $user ? true : false,
        'user_email_verified' => $user && $user->email_verified_at ? true : false,
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

// Log viewer route for debugging
Route::get('/debug/logs', function () {
    $logFile = storage_path('logs/laravel.log');
    
    if (!file_exists($logFile)) {
        return response()->json(['error' => 'Log file not found']);
    }
    
    $lines = request('lines', 100); // Default to last 100 lines
    $content = file($logFile);
    $totalLines = count($content);
    
    // Get the last N lines
    $logLines = array_slice($content, -$lines);
    
    return response()->json([
        'log_file' => $logFile,
        'total_lines' => $totalLines,
        'showing_lines' => count($logLines),
        'last_lines' => array_map('trim', $logLines),
        'timestamp' => now()->toDateTimeString()
    ]);
});

// Log viewer HTML interface
Route::get('/debug/log-viewer', function () {
    return '<!DOCTYPE html>
<html>
<head>
    <title>Laravel Log Viewer</title>
    <style>
        body { font-family: monospace; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .log-line { margin: 2px 0; padding: 5px; border-radius: 3px; }
        .error { background-color: #ffebee; color: #c62828; }
        .warning { background-color: #fff3e0; color: #ef6c00; }
        .info { background-color: #e3f2fd; color: #1565c0; }
        .debug { background-color: #f3e5f5; color: #7b1fa2; }
        .controls { margin-bottom: 20px; }
        button { padding: 10px 20px; margin: 5px; background: #2196f3; color: white; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #1976d2; }
        .refresh { background: #4caf50; }
        .clear { background: #f44336; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🔍 Laravel Log Viewer</h2>
        <div class="controls">
            <button onclick="loadLogs(50)" class="refresh">Last 50 Lines</button>
            <button onclick="loadLogs(100)" class="refresh">Last 100 Lines</button>
            <button onclick="loadLogs(200)" class="refresh">Last 200 Lines</button>
            <button onclick="clearLogs()" class="clear">Clear Logs</button>
            <button onclick="loadLogs()" class="refresh">Refresh</button>
        </div>
        <div id="logs">Loading logs...</div>
    </div>

    <script>
        function loadLogs(lines = 100) {
            fetch("/debug/logs?lines=" + lines)
                .then(response => response.json())
                .then(data => {
                    const logsDiv = document.getElementById("logs");
                    if (data.error) {
                        logsDiv.innerHTML = "<div class=\"error\">Error: " + data.error + "</div>";
                        return;
                    }
                    
                    let html = "<h3>Showing " + data.showing_lines + " of " + data.total_lines + " total lines</h3>";
                    html += "<div style=\"font-size: 12px; margin-bottom: 10px;\">Last updated: " + data.timestamp + "</div>";
                    
                    data.last_lines.forEach(line => {
                        let className = "";
                        if (line.includes("[ERROR]") || line.includes("ERROR:")) className = "error";
                        else if (line.includes("[WARNING]") || line.includes("WARNING:")) className = "warning";
                        else if (line.includes("[INFO]") || line.includes("INFO:")) className = "info";
                        else if (line.includes("[DEBUG]") || line.includes("DEBUG:")) className = "debug";
                        
                        html += "<div class=\"log-line " + className + "\">" + escapeHtml(line) + "</div>";
                    });
                    
                    logsDiv.innerHTML = html;
                });
        }
        
        function clearLogs() {
            if (confirm("Are you sure you want to clear the logs?")) {
                fetch("/debug/clear-logs", { method: "POST" })
                    .then(() => {
                        alert("Logs cleared!");
                        loadLogs();
                    });
            }
        }
        
        function escapeHtml(text) {
            const div = document.createElement("div");
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Auto refresh every 10 seconds
        setInterval(() => loadLogs(), 10000);
        
        // Load logs on page load
        loadLogs();
    </script>
</body>
</html>';
});

// Clear logs route
Route::post('/debug/clear-logs', function () {
    $logFile = storage_path('logs/laravel.log');
    if (file_exists($logFile)) {
        file_put_contents($logFile, '');
        return response()->json(['message' => 'Logs cleared']);
    }
    return response()->json(['error' => 'Log file not found']);
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
