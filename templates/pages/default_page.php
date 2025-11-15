<?php
/**
 * Default Page Template
 * Used as fallback when specific page template is not found
 */
if (!defined('CMS_SECURITY_CHECK')) {
    die('Invalid request');
}

// Basic security checks
if (!isset($_SESSION['user_authenticated'])) {
    header('Location: /login');
    exit;
}

// CSRF token check
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token()) {
    die('Invalid CSRF token');
}

// Role-based access control
if (!has_page_access($_SESSION['user_role'])) {
    header('HTTP/1.0 403 Forbidden');
    die('Access denied');
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title ?? 'CMS Page'); ?></title>
</head>
<body>
    <header>
        <h1><?php echo htmlspecialchars($page_title ?? 'Default Page'); ?></h1>
    </header>
    <main>
        <?php if (isset($page_content)): ?>            <?php echo $page_content;  ?>        <?php else: ?>
            <p>This is the default page template.</p>
        <?php endif;  ?>
    </main>
</body>
</html>
