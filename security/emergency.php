<?php
require_once __DIR__.'/../api-gateway/middlewares/authmiddleware.php';
require_once __DIR__.'/emergencymodecontroller.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

// Verify admin authentication
AuthMiddleware::verifyAdmin();

// Activate emergency mode
EmergencyModeController::activate();

// Return success response
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Emergency mode activated',
    'timestamp' => time()
]);
