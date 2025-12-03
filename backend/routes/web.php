<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AdminDashboardController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login');
});

Route::get('/health', function () {
    try {
        // Test database connection
        $dbStatus = \DB::connection()->getPdo() ? 'connected' : 'disconnected';
        
        return response()->json([
            'status' => 'healthy',
            'database' => $dbStatus,
            'timestamp' => now(),
            'laravel_version' => app()->version(),
            'environment' => app()->environment()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => now(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/test-admin', function () {
    try {
        return response()->json([
            'message' => 'Admin route is accessible',
            'auth_check' => \Auth::check(),
            'auth_user' => \Auth::user() ? \Auth::user()->toArray() : null,
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/test-dashboard', function () {
    try {
        // Test the dashboard stats method directly
        $controller = new \App\Http\Controllers\AdminDashboardController();
        
        // Test basic booking query without relationships
        $bookingCount = \App\Models\Booking::count();
        
        // Test the specific query causing issues
        $recentBookings = \App\Models\Booking::latest()->take(5)->get();
        
        return response()->json([
            'message' => 'Dashboard test successful',
            'booking_count' => $bookingCount,
            'recent_bookings' => $recentBookings->toArray(),
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

Route::get('/test-admin-controller', function () {
    try {
        // Test AdminDashboardController index method directly
        $controller = new \App\Http\Controllers\AdminDashboardController();
        
        // Mock authentication for testing
        \Auth::loginUsingId(3); // Use the user ID from earlier test
        
        // Try to call the index method
        $result = $controller->index();
        
        return response()->json([
            'message' => 'Controller index method works',
            'result_type' => get_class($result),
            'timestamp' => now()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
});

// Authentication routes
Route::middleware(['web'])->group(function () {
    Auth::routes();
});



Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Simple test admin route to isolate the issue
Route::get('/simple-admin', function () {
    return '<h1>Simple Admin Test</h1><p>Auth: ' . (\Auth::check() ? 'Logged in as ' . \Auth::user()->name : 'Not logged in') . '</p><p>If you see this, routing works fine.</p>';
})->middleware('auth');

// Admin Dashboard Routes (protected by auth middleware)
Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::get('/', function () {
        try {
            $controller = new AdminDashboardController();
            return $controller->index();
        } catch (\Exception $e) {
            // If view fails, show detailed error
            return '<h1>View Rendering Error</h1>' .
                   '<p><strong>Error:</strong> ' . $e->getMessage() . '</p>' .
                   '<p><strong>File:</strong> ' . $e->getFile() . '</p>' .
                   '<p><strong>Line:</strong> ' . $e->getLine() . '</p>' .
                   '<pre>' . $e->getTraceAsString() . '</pre>';
        }
    })->name('dashboard');
    
    // Route to test controller data without complex view
    Route::get('/debug', function () {
        try {
            $controller = new AdminDashboardController();
            \Auth::loginUsingId(3);
            $result = $controller->index();
            $data = $result->getData();
            
            return '<h1>Dashboard Debug</h1><pre>' . print_r($data, true) . '</pre>';
        } catch (\Exception $e) {
            return '<h1>Controller Error</h1><pre>' . $e->getMessage() . '</pre>';
        }
    });
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
    
    // Google Reviews Management
    Route::get('/reviews', [AdminDashboardController::class, 'reviews'])->name('reviews');
    Route::post('/reviews/sync', [AdminDashboardController::class, 'syncGoogleReviews'])->name('reviews.sync');
    Route::put('/reviews/{review}/toggle-status', [AdminDashboardController::class, 'toggleReviewStatus'])->name('reviews.toggle-status');
});

// Simple test route
Route::get('/test-route', function () {
    return response()->json([
        'message' => 'New routes are working!',
        'timestamp' => now()->toDateTimeString(),
        'routes_cached' => app()->routesAreCached(),
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
