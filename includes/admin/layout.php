<?php
/**
 * Admin Layout Template
 * 
 * Main wrapper for admin interface
 * Includes navigation and content sections
 * 
 * @package CMS
 * @subpackage Admin
 * @version 1.0.0
 */

// Verify admin access
require_once __DIR__ . '/../auth/AuthenticationMiddleware.php';
$auth = new AuthenticationMiddleware();
$auth->requireAdmin();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Admin</title>
    <link rel="stylesheet" href="/
includes/admin/admin.css">
</head>
<body>
    <div class="admin-container">
        <?php require_once __DIR__ . '/nav.php'; ?>
        <main class="admin-content">
            <?php 
            // Load requested admin section
            $section = $_GET['section'] ?? 'dashboard';
            $sectionFile = __DIR__ . '/' . $section . '.php';
            
            if (file_exists($sectionFile)) {
                require_once $sectionFile;
            } else {
                echo '
<div class="error">Invalid admin section</div>';
            }
            ?>
        </main>
    </div>
</body>
</html>
