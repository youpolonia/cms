<?php
// Verify session is active and valid

// Basic security checks
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/login.php');
    exit;
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES); ?>">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="/admin/assets/css/admin.css">
</head>
<body>
    <header class="admin-header">
        <div class="header-content">
            <h1>CMS Admin Panel</h1>
            <nav class="main-nav">
                <?php require_once __DIR__ . '/includes/nav.php'; 
?>            </nav>
        </div>
    </header>
    <main class="admin-main">
