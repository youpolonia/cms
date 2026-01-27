<?php
/**
 * CSRF Protection Middleware for Admin API Endpoints
 */
require_once __DIR__ . '/../../../core/csrf.php';

function verifyCSRFToken(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        return;
    }

    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
    if (!$token || !\Includes\Security\CSRF::validate($token)) {
        http_response_code(403);
        die(json_encode([
            'success' => false,
            'message' => 'Invalid CSRF token'
        ]));
    }
}
