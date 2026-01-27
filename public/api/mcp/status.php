<?php
header('Content-Type: application/json');

try {
    // Verify required PHP version
    if (version_compare(PHP_VERSION, '8.1.0', '<')) {
        throw new RuntimeException('PHP 8.1+ required');
    }

    // Verify JSON extension
    if (!function_exists('json_encode')) {
        throw new RuntimeException('JSON extension not available');
    }

    // Verify server connectivity
    $isConnected = true; // Placeholder for actual connectivity checks
    
    // Build response
    $response = [
        'status' => $isConnected ? 'OK' : 'ERROR',
        'version' => '1.0',
        'timestamp' => time(),
        'environment' => [
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
            'extensions' => get_loaded_extensions()
        ]
    ];

    if (!$isConnected) {
        $response['error'] = 'Connection failed';
    }

    http_response_code($isConnected ? 200 : 503);
    echo json_encode($response, JSON_PRETTY_PRINT);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'ERROR',
        'error' => $e->getMessage(),
        'timestamp' => time()
    ], JSON_PRETTY_PRINT);
}
