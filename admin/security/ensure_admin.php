<?php
/**
 * Ensure admin authentication
 * Simple wrapper for admin permission check
 */

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    require_once dirname(__DIR__, 2) . '/core/session_boot.php';
    cms_session_start('admin');
}

// Check if admin is logged in
if (empty($_SESSION['admin_id']) && empty($_SESSION['admin_authenticated'])) {
    http_response_code(401);
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => false, 'error' => 'Unauthorized']);
    } else {
        header('Location: /admin/login');
    }
    exit;
}
