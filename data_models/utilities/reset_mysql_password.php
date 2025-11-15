<?php
require_once __DIR__ . '/../../config.php';

// DEV_MODE security gate
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(404);
    echo json_encode(['error' => 'Not found']);
    exit;
}

/**
 * MySQL Password Reset Utility
 * Creates temporary init file to reset root password
 */

header('Content-Type: application/json');

// Stubbed for security compliance
http_response_code(403);
echo json_encode([
    'error' => 'reset_mysql_password.php disabled',
    'message' => 'This operation is not permitted in production'
]);
