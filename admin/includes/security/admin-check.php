<?php
/**
 * Admin Access Control and Role Verification
 *
 * Validates admin session and verifies user has required permissions
 *
 * Protected admin pages must define ADMIN_PROTECTED before including this file
 * Login page (login.php) should NOT define ADMIN_PROTECTED
 */
declare(strict_types=1);

// Skip checks if this is the login page
$current_script = basename($_SERVER['SCRIPT_NAME']);
if ($current_script === 'login.php') {
    return;
}

// Check if ADMIN_PROTECTED is defined (required for all protected pages)
if (!defined('ADMIN_PROTECTED')) {
    define('MISSING_GUARD', true);
    return;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    require_once __DIR__ . '/../../../core/session_boot.php';
    cms_session_start('admin');
}

// Verify admin is logged in
if (empty($_SESSION['admin_logged_in'])) {
    header('Location: /admin/login.php?error=session_expired');
    exit;
}

// Verify CSRF token if this is a POST request
require_once __DIR__ . '/../../../core/csrf.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST[CSRF::getTokenName()] ?? null;
    if (!$token || !CSRF::validateToken($token)) {
        header('HTTP/1.1 403 Forbidden');
        exit('Invalid CSRF token');
    }
}

// Generate and output CSRF token for AJAX responses
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    header('X-CSRF-Token: ' . CSRF::generateToken());
}

// Verify session security parameters
if (empty($_SESSION['ip_address']) || $_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    header('HTTP/1.1 403 Forbidden');
    exit('Session security violation');
}

// Regenerate session ID periodically to prevent fixation
if (empty($_SESSION['last_regeneration']) || time() - $_SESSION['last_regeneration'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
