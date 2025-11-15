<?php
/**
 * Admin Header Template
 * Includes: CSRF protection, session validation, core assets
 */
// Skip guard for static assets
$isStaticAsset = str_starts_with($_SERVER['REQUEST_URI'] ?? '', '/admin/assets/');
if (!defined('CMS_ADMIN') && !$isStaticAsset) {
    define('MISSING_GUARD', true);
    return;
}

// Verify admin session (skip for static assets and login page)
if (!$isStaticAsset && !str_contains($_SERVER['SCRIPT_NAME'] ?? '', 'login.php')) {
    require_once __DIR__.'/../includes/security/admin-check.php';
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle ?? 'Admin Panel'); ?></title>
    <link rel="stylesheet" href="/admin/assets/css/core.css">
    <script src="/admin/assets/js/core.js" defer></script>
    <meta name="csrf-token" content="<?php echo Security::getCSRFToken(); ?>">
</head>
<body class="admin-template">
    <header class="admin-header">
        <div class="admin-branding">
            <h1>CMS Admin Panel</h1>
            <div class="admin-version">v<?php echo CMS_VERSION; ?></div>
        </div>
        <nav class="admin-nav">
            <ul>
                <li><a href="/admin/dashboard">Dashboard</a></li>
                <li><a href="/admin/content">Content</a></li>
                <li><a href="/admin/users">Users</a></li>
                <li><a href="/admin/settings">Settings</a></li>
            </ul>
        </nav>
        <div class="admin-user">
            <?php echo htmlspecialchars($_SESSION['admin_username'] ?? ''); 
?>        </div>
    </header>
    <main class="admin-main">
