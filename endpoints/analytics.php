<?php
require_once __DIR__ . '/../core/analyticscollector.php';
require_once __DIR__.'/../core/tenantmiddleware.php';

header('Content-Type: application/json');

try {
    $tenantId = TenantMiddleware::getTenantId();
    $payload = json_decode(file_get_contents('php://input'), true);
    
    if (!AnalyticsCollector::track($payload['metric'], $payload['value'], $tenantId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid metric']);
        exit;
    }

    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
