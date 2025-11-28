<?php
/**
 * API Test Script for admin.tfcmockup.com
 * Place this in the public folder and access via: https://admin.tfcmockup.com/test-api.php
 */

echo "<h2>Laravel API Test - admin.tfcmockup.com</h2>";

// Test 1: Check if Laravel is loading
echo "<h3>1. Laravel Bootstrap Test:</h3>";
try {
    require_once __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "✅ Laravel bootstrap successful<br>";
} catch (Exception $e) {
    echo "❌ Laravel bootstrap failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test 2: Check environment
echo "<h3>2. Environment:</h3>";
echo "APP_ENV: " . env('APP_ENV', 'not set') . "<br>";
echo "APP_DEBUG: " . (env('APP_DEBUG') ? 'true' : 'false') . "<br>";
echo "APP_URL: " . env('APP_URL', 'not set') . "<br>";

// Test 3: Check database connection
echo "<h3>3. Database Test:</h3>";
try {
    $app->make('kernel')->bootstrap();
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connection successful<br>";
    
    $eventCount = DB::table('events')->count();
    echo "Events in database: " . $eventCount . "<br>";
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 4: Check routes
echo "<h3>4. Route Test:</h3>";
try {
    $routes = Route::getRoutes();
    $apiRoutes = 0;
    foreach ($routes as $route) {
        if (strpos($route->uri(), 'api/public') !== false) {
            $apiRoutes++;
            echo "Found route: " . $route->methods()[0] . " " . $route->uri() . "<br>";
        }
    }
    echo "Total API routes found: " . $apiRoutes . "<br>";
} catch (Exception $e) {
    echo "❌ Route error: " . $e->getMessage() . "<br>";
}

// Test 5: Direct API call
echo "<h3>5. Direct API Test:</h3>";
try {
    $controller = new App\Http\Controllers\API\PublicEventController();
    $events = $controller->index();
    echo "✅ API controller accessible<br>";
    echo "Response type: " . get_class($events) . "<br>";
} catch (Exception $e) {
    echo "❌ API controller error: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Troubleshooting Links:</h3>";
echo "<a href='/api/public/events' target='_blank'>Test API: /api/public/events</a><br>";
echo "<a href='/' target='_blank'>Laravel Welcome Page</a><br>";

// Clean up
if (function_exists('app')) {
    app()->flush();
}
?>