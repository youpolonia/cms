<?php
// config/bootstrap.php - Error Logging Configuration
// Last updated: 2025-06-18 by Roo (Code Mode)

// Ensure logs directory exists
if (!is_dir(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0755, true);
}

// Set error reporting level
error_reporting(E_ALL);

// Configure error logging
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../logs/error-' . date('Y-m-d') . '.log');

// Custom error handler for better logging
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    $logEntry = sprintf(
        "[%s] PHP %s: %s in %s on line %d",
        date('Y-m-d H:i:s'),
        self::getErrorType($errno),
        $errstr,
        $errfile,
        $errline
    );
    
    error_log($logEntry);
    
    // Don't execute PHP internal error handler
    return true;
});

// Helper function to get error type name
function getErrorType($type) {
    $types = [
        E_ERROR             => 'ERROR',
        E_WARNING           => 'WARNING',
        E_PARSE             => 'PARSE',
        E_NOTICE            => 'NOTICE',
        E_CORE_ERROR        => 'CORE_ERROR',
        E_CORE_WARNING      => 'CORE_WARNING',
        E_COMPILE_ERROR     => 'COMPILE_ERROR',
        E_COMPILE_WARNING   => 'COMPILE_WARNING',
        E_USER_ERROR        => 'USER_ERROR',
        E_USER_WARNING      => 'USER_WARNING',
        E_USER_NOTICE       => 'USER_NOTICE',
        E_STRICT            => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED        => 'DEPRECATED',
        E_USER_DEPRECATED   => 'USER_DEPRECATED'
    ];
    
    return $types[$type] ?? 'UNKNOWN';
}
