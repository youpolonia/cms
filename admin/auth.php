<?php
require_once __DIR__ . '/../includes/session_config.php';
require_once __DIR__ . '/../includes/security_enhancements.php';
require_once __DIR__ . '/../includes/controllers/Auth/adminauthcontroller.php';

// Apply security headers
applySecurityHeaders();

// Handle authentication
$controller = new AdminAuthController();
$controller->authenticate();
