<?php
/**
 * Error Template
 */
header('Content-Type: text/html');
$statusCode = http_response_code();
?><!DOCTYPE html>
<html>
<head>
    <title>Error</title>
    <style>
        body { font-family: sans-serif; margin: 2rem; }
        .error { background: #f8d7da; padding: 1rem; border-radius: 4px; }
    </style>
</head>
<body>
    <?php if ($statusCode === 404): ?>
        <h1>Page Not Found</h1>
        <div class="error">
            <p>The requested page could not be found.</p>
            <p><a href="/">Return to homepage</a></p>
        </div>
    <?php else: ?>
        <h1>An Error Occurred</h1>
        <div class="error">
            <p>Error ID: <?= htmlspecialchars($errorId ?? 'UNKNOWN') ?></p>
            <p>Please try again later or contact support if the problem persists.</p>
        </div>
    <?php endif; ?>
    </div>
</body>
</html>
