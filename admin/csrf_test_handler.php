<?php
/**
 * CSRF Test Handler
 * Validates CSRF token submission
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

cms_session_start('admin');


// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSRF Test Result</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        pre { background: #f0f0f0; padding: 10px; }
    </style>
</head>
<body>
    <h1>CSRF Validation Test Result</h1>

    <?php
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $sentToken = $_POST['csrf_token'] ?? '';
    $sessionToken = csrf_token();

    echo "<p><strong>Request Method:</strong> $method</p>";
    echo "<p><strong>Token from POST:</strong> " . htmlspecialchars($sentToken) . "</p>";
    echo "<p><strong>Token from Session:</strong> " . htmlspecialchars($sessionToken) . "</p>";

    if ($method === 'POST') {
        if (empty($sessionToken)) {
            echo '<p class="error">ERROR: No session token found!</p>';
        } elseif (empty($sentToken)) {
            echo '<p class="error">ERROR: No token submitted in form!</p>';
        } elseif (!hash_equals($sessionToken, $sentToken)) {
            echo '<p class="error">ERROR: Tokens do not match!</p>';
        } else {
            echo '<p class="ok">SUCCESS: CSRF validation passed!</p>';
        }
    } else {
        echo '<p class="error">ERROR: Not a POST request</p>';
    }
    ?>

    <p><a href="csrf_diagnostic.php">Back to Diagnostic</a></p>

    <h2>Debug Info</h2>
    <p><strong>$_POST:</strong></p>
    <pre><?= htmlspecialchars(print_r($_POST, true)) ?></pre>

    <p><strong>$_SESSION:</strong></p>
    <pre><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
</body>
</html>
