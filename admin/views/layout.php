<?php
/**
 * Admin Layout Template - Framework-free version
 */
if (!defined('ADMIN_SECURITY')) {
    die('Direct access denied');
}

// Standardized header require_once
require_once __DIR__ . '/admin_header.php';

// Main content section
if (isset($content)) {
    echo $content;
} else {
    echo 'No content provided';
}

// Standardized footer require_once  
require_once __DIR__ . '/admin_footer.php';

// Basic security checks
function admin_check_auth() {
    if (empty($_SESSION['admin_logged_in'])) {
        header('Location: /admin/login.php');
        exit;
    }
}
