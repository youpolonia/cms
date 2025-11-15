<?php
require_once __DIR__.'/../includes/service/serviceintegrationhandler.php';
require_once __DIR__.'/../includes/middleware/apiauthmiddleware.php';

header('Content-Type: application/json');

// Apply authentication middleware
$authResult = ApiAuthMiddleware::authenticate();
if ($authResult !== true) {
    echo json_encode($authResult);
    exit;
}

// Service discovery endpoint
if ($_SERVER['REQUEST_METHOD'] === 'GET' && $_SERVER['REQUEST_URI'] === '/api/services') {
    $services = serviceintegrationhandler::discoverServices();
    echo json_encode([
        'status' => 'success',
        'data' => $services
    ]);
    exit;
}

// Service health check endpoint
if ($_SERVER['REQUEST_METHOD'] === 'GET' && preg_match('/^\/api\/services\/([^\/]+)\/health$/', $_SERVER['REQUEST_URI'], $matches)) {
    $serviceName = $matches[1];
    $status = serviceintegrationhandler::checkServiceHealth($serviceName);
    echo json_encode([
        'status' => 'success',
        'data' => [
            'service' => $serviceName,
            'health_status' => $status
        ]
    ]);
    exit;
}

http_response_code(404);
echo json_encode(['status' => 'error', 'message' => 'Endpoint not found']);
