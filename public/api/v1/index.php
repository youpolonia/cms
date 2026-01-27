<?php
// API v1 Router
header('Content-Type: application/json');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// API version header
header('X-API-Version: v1');

// Get request path
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$endpoint = str_replace('/api/v1/', '', $request);

// Rate limiting setup
require_once __DIR__ . '/../../../core/contentservice.php';
require_once __DIR__ . '/../../../core/ratelimiter.php';

$tenantId = $_SERVER['HTTP_X_TENANT_CONTEXT'] ?? 'default';
$rateLimiter = new RateLimiter($tenantId);

if (!$rateLimiter->check()) {
    http_response_code(429);
    die(json_encode(['error' => 'Rate limit exceeded']));
}

// Route requests
switch ($endpoint) {
    case 'content':
        require_once __DIR__ . '/content.php';
        break;
    case 'blog':
        require_once __DIR__ . '/blog.php';
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}
