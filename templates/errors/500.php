<?php
/**
 * Server Error (500) Template
 * Shows error details in development, generic message in production
 */

$isDev = ($_ENV['APP_ENV'] ?? 'production') === 'development';
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$error = $error ?? 'An unexpected error occurred';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error</title>
    <style>
        body { font-family: sans-serif; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .error-details { background: #f8f8f8; padding: 20px; margin: 20px 0; }
        .admin-actions { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Server Error</h1>
        <p>We're sorry, but something went wrong.</p>

        <?php if ($isDev || $isAdmin): ?>
            <div class="error-details">
                <h2>Error Details</h2>
                <p><?= htmlspecialchars($error, ENT_QUOTES) ?></p>
                <?php if (isset($exception) && $exception instanceof Exception): ?>
                    <p><strong>File:</strong> <?= $exception->getFile() ?>:<?= $exception->getLine() ?></p>
                    <pre><?= htmlspecialchars($exception->getTraceAsString()) ?></pre>
                <?php endif;  ?>
            </div>
        <?php endif;  ?>
        <?php if ($isAdmin): ?>
            <div class="admin-actions">
                <p><a href="/admin/dashboard">Return to Dashboard</a></p>
            </div>
        <?php else: ?>
            <p><a href="/">Return to Homepage</a></p>
        <?php endif;  ?>
    </div>
</body>
</html>
