<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../includes/controllers/auth/authcontroller.php';

header('Content-Type: application/json');

try {
    $result = \Controllers\AuthController::logout();
    echo json_encode(['success' => $result]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
