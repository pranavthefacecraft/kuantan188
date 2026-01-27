<?php
// Simple environment configuration checker
// Save as: backend/public/check-billplz-config.php

// Get Billplz configuration values
echo "<h2>🔍 Billplz Configuration Check</h2>";

echo "<h3>Environment Variables (from .env file):</h3>";
echo "<pre>";

$billplzEnvVars = [
    'BILLPLZ_API_KEY',
    'BILLPLZ_COLLECTION_ID', 
    'BILLPLZ_X_SIGNATURE_KEY',
    'BILLPLZ_SECRET_KEY',
    'BILLPLZ_ENVIRONMENT',
    'BILLPLZ_SANDBOX_URL',
    'BILLPLZ_PRODUCTION_URL',
    'BILLPLZ_CALLBACK_URL',
    'BILLPLZ_REDIRECT_URL'
];

$allConfigured = true;

foreach ($billplzEnvVars as $var) {
    $value = $_ENV[$var] ?? getenv($var);
    $status = empty($value) ? "❌ MISSING" : "✅ SET";
    
    if (empty($value)) {
        $allConfigured = false;
    }
    
    // Mask sensitive values for display
    if (!empty($value) && in_array($var, ['BILLPLZ_API_KEY', 'BILLPLZ_X_SIGNATURE_KEY', 'BILLPLZ_SECRET_KEY'])) {
        $displayValue = substr($value, 0, 8) . '***';
    } else {
        $displayValue = $value ?: 'NOT SET';
    }
    
    echo "{$var}: {$status} - {$displayValue}\n";
}

echo "</pre>";

echo "<h3>Laravel Config Values:</h3>";
echo "<pre>";

// Try to load Laravel config if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    try {
        // Initialize Laravel app
        $app = require_once __DIR__ . '/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        
        $configValues = [
            'services.billplz.api_key',
            'services.billplz.collection_id',
            'services.billplz.x_signature_key',
            'services.billplz.environment',
            'services.billplz.sandbox_url',
            'services.billplz.production_url',
            'services.billplz.callback_url',
            'services.billplz.redirect_url'
        ];
        
        foreach ($configValues as $configKey) {
            $value = config($configKey);
            $status = empty($value) ? "❌ MISSING" : "✅ SET";
            
            // Mask sensitive values for display
            if (!empty($value) && strpos($configKey, 'api_key') !== false) {
                $displayValue = substr($value, 0, 8) . '***';
            } else {
                $displayValue = $value ?: 'NOT SET';
            }
            
            echo "{$configKey}: {$status} - {$displayValue}\n";
        }
        
    } catch (Exception $e) {
        echo "Could not load Laravel config: " . $e->getMessage() . "\n";
    }
} else {
    echo "Laravel not available for config check\n";
}

echo "</pre>";

echo "<h3>Summary:</h3>";
if ($allConfigured) {
    echo "<p style='color: green;'>✅ All Billplz environment variables appear to be configured!</p>";
} else {
    echo "<p style='color: red;'>❌ Some Billplz environment variables are missing. Please add them to your .env file:</p>";
    echo "<pre>";
    echo "# Add these to your .env file:\n";
    echo "BILLPLZ_API_KEY=your_api_key_here\n";
    echo "BILLPLZ_COLLECTION_ID=your_collection_id_here\n";
    echo "BILLPLZ_X_SIGNATURE_KEY=your_x_signature_key_here\n";
    echo "BILLPLZ_SECRET_KEY=your_secret_key_here\n";
    echo "BILLPLZ_ENVIRONMENT=sandbox\n";
    echo "BILLPLZ_CALLBACK_URL=https://tickets.tfcmockup.com/payment/callback\n";
    echo "BILLPLZ_REDIRECT_URL=https://tickets.tfcmockup.com/payment/callback\n";
    echo "</pre>";
}

echo "<h3>Next Steps:</h3>";
echo "<ul>";
echo "<li>1. Get your Billplz API credentials from <a href='https://www.billplz.com/enterprise/setting' target='_blank'>Billplz Dashboard</a></li>";
echo "<li>2. Add the missing environment variables to your server's .env file</li>";
echo "<li>3. Run: <code>php artisan config:cache</code> to refresh the configuration</li>";
echo "<li>4. Test the payment again</li>";
echo "</ul>";

echo "<h3>Test Payment API:</h3>";
echo "<p>Once configured, you can test the payment endpoint:</p>";
echo "<pre>";
echo "curl -X POST \"https://admin.tfcmockup.com/api/public/payment/billplz/create\" \\\n";
echo "  -H \"Content-Type: application/json\" \\\n";
echo "  -d '{\"booking_id\": 21, \"amount\": 100, \"customer_name\": \"Test\", \"customer_email\": \"test@example.com\", \"description\": \"Test Payment\"}'";
echo "</pre>";
?>