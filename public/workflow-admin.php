<?php
require_once __DIR__ . '/../core/bootstrap.php';
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', dirname(__DIR__)); // Resolves to /var/www/html/cms
}
// Security headers
header("Content-Security-Policy: default-src 'self'");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

// Include workflow admin with document root path
require_once __DIR__ . '/../admin/workflows/workflow_admin.php';
