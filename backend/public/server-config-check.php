<?php
// Simple script to check current server configuration
echo "<h2>Server Configuration Check</h2>";
echo "<strong>APP_ENV:</strong> " . (getenv('APP_ENV') ?: config('app.env')) . "<br>";
echo "<strong>APP_URL:</strong> " . (getenv('APP_URL') ?: config('app.url')) . "<br>";
echo "<strong>SESSION_DRIVER:</strong> " . (getenv('SESSION_DRIVER') ?: config('session.driver')) . "<br>";
echo "<strong>SESSION_DOMAIN:</strong> " . (getenv('SESSION_DOMAIN') ?: config('session.domain')) . "<br>";
echo "<strong>SESSION_SECURE:</strong> " . (getenv('SESSION_SECURE_COOKIE') ?: config('session.secure')) . "<br>";
echo "<strong>Current Domain:</strong> " . $_SERVER['HTTP_HOST'] . "<br>";
echo "<strong>Is HTTPS:</strong> " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'Yes' : 'No') . "<br>";
echo "<strong>Session Path:</strong> " . session_save_path() . "<br>";
echo "<strong>Session Status:</strong> " . session_status() . "<br>";
echo "<strong>PHP Session ID:</strong> " . session_id() . "<br>";