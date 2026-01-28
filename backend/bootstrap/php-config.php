<?php

// Set PHP configuration for local development with remote database
if (env('APP_ENV') === 'local') {
    $maxExecutionTime = env('MAX_EXECUTION_TIME', 300);
    $maxInputTime = env('MAX_INPUT_TIME', 300);
    $memoryLimit = env('MEMORY_LIMIT', '512M');
    
    ini_set('max_execution_time', $maxExecutionTime);
    ini_set('max_input_time', $maxInputTime);
    ini_set('memory_limit', $memoryLimit);
    
    // Additional settings for remote database connections
    ini_set('default_socket_timeout', 300);
    ini_set('mysql.connect_timeout', 300);
}