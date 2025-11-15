<?php

require_once __DIR__ . '/../controllers/analyticscontroller.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SERVER['REQUEST_URI'] === '/analytics/track') {
    $input = json_decode(file_get_contents('php://input'), true);
    $response = AnalyticsController::trackEvent($input);
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

http_response_code(404);
echo json_encode(['error' => 'Not found']);
