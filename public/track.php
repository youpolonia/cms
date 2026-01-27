<?php
require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../analytics/TrackingService.php';

header('Content-Type: application/json');

try {
    $pageUrl = $_SERVER['REQUEST_URI'] ?? '/';
    $referrer = $_SERVER['HTTP_REFERER'] ?? null;
    
    $trackingData = TrackingService::trackPageView($pageUrl, $referrer);
    echo json_encode([
        'status' => 'success',
        'data' => $trackingData
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
