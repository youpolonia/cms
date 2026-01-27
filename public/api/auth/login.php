<?php
require_once __DIR__ . '/../../../core/bootstrap.php';
require_once __DIR__ . '/../../../includes/controllers/auth/authcontroller.php';
require_once __DIR__ . '/../../../includes/auth/jwt.php';
require_once __DIR__ . '/../../../core/responsehandler.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        ResponseHandler::sendError('Invalid JSON input');
        exit;
    }
    
    $result = \Controllers\AuthController::login($input);
    ResponseHandler::sendSuccess($result);
} catch (Exception $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit;
}
