<?php
declare(strict_types=1);

require_once __DIR__ . '/gateway.php';
require_once __DIR__ . '/logger.php';

// .env loading removed — config comes only from root/config.php

// Load appropriate config based on environment
$environment = $_ENV['APP_ENV'] ?? 'dev';
if ($environment === 'production' && file_exists(__DIR__ . '/config.prod.php')) {
    $config = require_once __DIR__ . '/config.prod.php';
} else {
    $config = require_once __DIR__ . '/config.php';
}

// Initialize services
$gateway = new Api\Gateway\Gateway($config);
$logger = new Api\Gateway\Logger();

// Add error handling middleware
$gateway->addMiddleware(function(array $request) use ($logger): array {
    try {
        return $request;
    } catch (\Throwable $e) {
        $logger->logError($e);
        throw $e;
    }
});

// Add rate limiting middleware
$gateway->addMiddleware(function(array $request) use ($config): array {
    if (!$config['rate_limits']['enabled']) {
        return $request;
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $cacheKey = "rate_limit_{$ip}_{$request['path']}";
    $cacheFile = __DIR__ . "/../cache/{$cacheKey}.cache";

    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data['timestamp'] > time() - 60 && $data['count'] >= $config['rate_limits']['default']) {
            throw new RuntimeException('Rate limit exceeded', 429);
        }
    }

    $count = ($data['count'] ?? 0) + 1;
    file_put_contents($cacheFile, json_encode([
        'timestamp' => time(),
        'count' => $count
    ]));

    return $request;
});

// Add method validation middleware
$gateway->addMiddleware(function(array $request) use ($config): array {
    $path = $request['path'] ?? '';
    if (!isset($config['services'][$path]['methods'])) {
        return $request;
    }

    if (!in_array($request['method'], $config['services'][$path]['methods'])) {
        throw new RuntimeException('Method not allowed', 405);
    }
    return $request;
});

// Add response logging middleware
$gateway->addMiddleware(function(array $request) use ($logger): array {
    $response = $request;
    $logger->logRequest($request, [
        'code' => $response['status'] ?? 200
    ]);
    return $response;
});

// Process the incoming request
$request = [
    'path' => parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH),
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'body' => file_get_contents('php://input')
];

try {
    $response = $gateway->handleRequest($request);
    http_response_code($response['code']);
    header('Content-Type: application/json');
    echo json_encode($response['data']);
} catch (Throwable $e) {
    http_response_code($e->getCode() ?: 500);
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
