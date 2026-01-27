<?php
/**
 * CSRF Diagnostic Tool
 * Helps debug CSRF verification issues
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/init.php'; // Must be before permissions check - starts session
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

// Start session and CSRF
cms_session_start('admin');

// RBAC: Require admin access
require_once __DIR__ . '/includes/permissions.php';
cms_require_admin_role();
csrf_boot('admin');

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>CSRF Diagnostic</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .section { background: white; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .ok { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        h2 { margin-top: 0; }
        pre { background: #f0f0f0; padding: 10px; overflow: auto; }
    </style>
</head>
<body>
    <h1>CSRF Diagnostic Tool</h1>

    <div class="section">
        <h2>Session Information</h2>
        <p><strong>Session Status:</strong> <?= session_status() === PHP_SESSION_ACTIVE ? '<span class="ok">ACTIVE</span>' : '<span class="error">NOT ACTIVE</span>' ?></p>
        <p><strong>Session Name:</strong> <?= htmlspecialchars(session_name()) ?></p>
        <p><strong>Session ID:</strong> <?= htmlspecialchars(session_id()) ?></p>
        <p><strong>Cookie Params:</strong></p>
        <pre><?= htmlspecialchars(print_r(session_get_cookie_params(), true)) ?></pre>
    </div>

    <div class="section">
        <h2>CSRF Token</h2>
        <?php $token = csrf_token(); ?>
        <p><strong>Token Exists:</strong> <?= !empty($token) ? '<span class="ok">YES</span>' : '<span class="error">NO</span>' ?></p>
        <p><strong>Token Value:</strong> <?= htmlspecialchars($token) ?></p>
        <p><strong>Token Length:</strong> <?= strlen($token) ?> characters</p>
    </div>

    <div class="section">
        <h2>Session Data</h2>
        <pre><?= htmlspecialchars(print_r($_SESSION, true)) ?></pre>
    </div>

    <div class="section">
        <h2>Cookies Sent by Browser</h2>
        <pre><?= htmlspecialchars(print_r($_COOKIE, true)) ?></pre>
    </div>

    <div class="section">
        <h2>Test CSRF Validation</h2>
        <form method="post" action="csrf_test_handler.php">
            <?php csrf_field(); ?>
            <button type="submit">Submit Test Form</button>
        </form>
    </div>

    <div class="section">
        <h2>HTTPS Detection</h2>
        <?php
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
              || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        ?>
        <p><strong>HTTPS Detected:</strong> <?= $https ? '<span class="ok">YES</span>' : '<span class="error">NO</span>' ?></p>
        <p><strong>$_SERVER['HTTPS']:</strong> <?= isset($_SERVER['HTTPS']) ? htmlspecialchars($_SERVER['HTTPS']) : 'not set' ?></p>
        <p><strong>$_SERVER['HTTP_X_FORWARDED_PROTO']:</strong> <?= isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? htmlspecialchars($_SERVER['HTTP_X_FORWARDED_PROTO']) : 'not set' ?></p>
    </div>
</body>
</html>
