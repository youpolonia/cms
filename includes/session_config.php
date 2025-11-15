<?php
// Session configuration for CMS
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime'  => 0,
        'path'      => '/',
        'domain'    => '',
        'secure'    => false,   // localhost
        'httponly'  => true,
        'samesite'  => 'Strict'
    ]);
}

// Store tenant context in session
if (!isset($_SESSION['tenant_id']) && isset($_SESSION['user'])) {
    $_SESSION['tenant_id'] = $_SESSION['user']->tenant_id ?? 'default';
}

// Initialize admin session variables
if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['admin_logged_in'] = false;
    $_SESSION['last_activity'] = time();
    $_SESSION['permissions'] = [];
}
