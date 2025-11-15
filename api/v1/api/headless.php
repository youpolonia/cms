<?php
/**
 * Headless CMS API Endpoint
 * Version: 1.0
 */

require_once __DIR__ . '/../../includes/auth/JWTManager.php';

// Enable CORS for headless access
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// JWT Authentication Middleware
try {
    $jwtManager = new JWTManager();
    $token = $jwtManager->getBearerToken();
    $payload = $jwtManager->validateToken($token);
    
    // Set authenticated user context
    define('API_USER_ID', $payload->userId);
} catch (Exception $e) {
    http_response_code(401);
    die(json_encode(['error' => 'Unauthorized: ' . $e->getMessage()]));
}

// Rate Limiting Middleware
$rateLimiter = new RateLimiter($_SERVER['REMOTE_ADDR']);
if (!$rateLimiter->checkLimit()) {
    http_response_code(429);
    die(json_encode(['error' => 'Too many requests']));
}

// Content-Type header
header('Content-Type: application/json');

// Route requests
$requestMethod = $_SERVER['REQUEST_METHOD'];
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$endpoints = [
    '/api/v1/content' => 'handleContentRequests',
    '/api/v1/users' => 'handleUserRequests',
    '/api/v1/media' => 'handleMediaRequests'
];

if (array_key_exists($requestUri, $endpoints)) {
    call_user_func($endpoints[$requestUri], $requestMethod);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Endpoint not found']);
}

function handleContentRequests($method) {
    // Implementation for content endpoints
}

function handleUserRequests($method) {
    // Implementation for user endpoints  
}

function handleMediaRequests($method) {
    // Implementation for media endpoints
}
