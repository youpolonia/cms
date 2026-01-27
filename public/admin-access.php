<?php
require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    require_once __DIR__ . '/../core/csrf.php';
    csrf_validate_or_403();
}

/**
 * Admin Panel Direct Access Point
 * File-based authentication system
 */

// Configuration
define('AUTH_FILE', __DIR__.'/../admin/auth/.admin_auth');
define('ADMIN_DASHBOARD', '/admin/dashboard.php');
define('SESSION_TIMEOUT', 3600); // 1 hour

// Start session using CMS session manager
cms_session_start('admin');

// Check if authentication file exists
if (!file_exists(AUTH_FILE)) {
    // Create default auth file if missing
    file_put_contents(AUTH_FILE, 'admin:' . password_hash('default_password', PASSWORD_DEFAULT));
    chmod(AUTH_FILE, 0600);
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $credentials = file(AUTH_FILE, FILE_IGNORE_NEW_LINES);
    foreach ($credentials as $line) {
        list($user, $hash) = explode(':', $line, 2);
        if ($user === $_POST['username'] && password_verify($_POST['password'], $hash)) {
            $_SESSION['admin_authenticated'] = true;
            $_SESSION['admin_user'] = $user;
            $_SESSION['admin_last_activity'] = time();
            header('Location: ' . ADMIN_DASHBOARD);
            exit;
        }
    }
    $error = 'Invalid credentials';
}

// Check session authentication
$authenticated = isset($_SESSION['admin_authenticated']) 
    && $_SESSION['admin_authenticated'] 
    && (time() - $_SESSION['admin_last_activity']) < SESSION_TIMEOUT;

if ($authenticated) {
    $_SESSION['admin_last_activity'] = time();
    header('Location: ' . ADMIN_DASHBOARD);
    exit;
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .login-container { max-width: 400px; margin: 100px auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #0073aa; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        button:hover { background: #006799; }
        .error { color: red; margin-bottom: 15px; }
        .fallback { margin-top: 30px; padding: 15px; background: #f0f0f0; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if (isset($error)): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <form method="post">
<?= csrf_field(); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
 required>
?>            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
 required>
?>            </div>
            <button type="submit">Login</button>
        </form>

        <div class="fallback">
            <h3>Fallback PHP Server</h3>
            <p>If the admin panel isn't loading, you can start a local PHP development server:</p>
            <pre>php -S localhost:8080 -t public</pre>
            <p>Then access the admin panel at:</p>
            <pre>http://localhost:8080/admin-access.php</pre>
        </div>
    </div>
</body>
</html>
