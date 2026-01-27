<?php
require_once __DIR__ . '/../../core/bootstrap.php';
require_once __DIR__ . '/../../core/auth.php';

// Secure session handling
if (!AuthHandler::isAdmin()) {
    header('Location: login.php');
    exit;
}

// Output buffering for security
ob_start();

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Admin Dashboard</title>
    <link rel="stylesheet" href="/css/styles.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1>CMS Administration</h1>
            <nav class="admin-nav">
                <ul>
                    <li><a href="/admin/" class="active">Dashboard</a></li>
                    <li><a href="/admin/content">Content</a></li>
                    <li><a href="/admin/media">Media</a></li>
                    <li><a href="/admin/users">Users</a></li>
                    <li><a href="/admin/settings">Settings</a></li>
                    <li><a href="/admin/logout.php">Logout</a></li>
                </ul>
            </nav>
        </header>

        <main class="admin-main">
            <section class="dashboard-widgets">
                <div class="widget">
                    <h2>Recent Content</h2>
                    <?php // Content listing would go here 
?>                </div>
                <div class="widget">
                    <h2>System Status</h2>
                    <p>CMS Version: 1.0.0</p>
                    <p>PHP Version: <?php echo phpversion(); ?></p>
                </div>
            </section>
        </main>

        <footer class="admin-footer">
            <p>&copy; <?php echo date('Y'); ?> CMS Admin Interface</p>
        </footer>
    </div>
</body>
</html>
<?php
ob_end_flush();
