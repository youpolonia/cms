<?php
require_once __DIR__ . '/../includes/security/securityauditorcontroller.php';

header('Content-Type: application/json');

try {
    $request = json_decode(file_get_contents('php://input'), true) ?? [];
    $response = Security\SecurityAuditorController::handleRequest($request);
    echo json_encode($response);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    ]);
}
