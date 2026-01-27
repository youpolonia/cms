<?php
if (file_exists(__DIR__ . '/../../core/bootstrap.php')) {
    require_once __DIR__ . '/../../core/bootstrap.php';
} else {
    error_log("Missing core/bootstrap.php — file not found");
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../core/session_boot.php';
require_once __DIR__ . '/../../core/csrf.php';
csrf_boot('admin');

cms_session_start('admin');

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/');
    exit;
}

require_once __DIR__ . '/../../includes/utilities/security/passwordutils.php';
$rl_path = __DIR__ . '/../../includes/ratelimiter.php';
if (file_exists($rl_path)) {
    $rlBase = realpath(__DIR__ . '/../../includes');
    $rlTarget = realpath($rl_path);
    if (!$rlTarget || !str_starts_with($rlTarget, $rlBase . DIRECTORY_SEPARATOR) || !is_file($rlTarget)) {
        error_log("SECURITY: blocked dynamic include: ratelimiter.php");
    } else {
        require_once $rlTarget;
    }
}

if (file_exists(__DIR__ . '/../../config/credentials.php')) {
    $credentials = require_once __DIR__ . '/../../config/credentials.php';
} else {
    error_log('Missing config/credentials.php');
    die('Configuration error: credentials.php not found');
}

$valid_username = $credentials['admin']['username'];
$valid_password_hash = $credentials['admin']['password_hash'];

if (empty($valid_password_hash)) {
    die('Admin password not configured. Please set admin.password_hash in config/credentials.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (class_exists('RateLimiter') && method_exists('RateLimiter','isLoginAllowed')) {
        if (!RateLimiter::isLoginAllowed($ip)) {
            $error = 'Too many login attempts. Please try again later.';
        }
    }
    csrf_validate_or_403();

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (empty($error) && $username === $valid_username && PasswordUtils::verify($password, $valid_password_hash)) {
        if (class_exists('RateLimiter') && method_exists('RateLimiter','resetAttempts')) {
            RateLimiter::resetAttempts($ip);
        }
        $_SESSION['admin_logged_in'] = true;
        session_regenerate_id(true);
        header('Location: /admin/');
        exit;
    } else {
        if (class_exists('RateLimiter') && method_exists('RateLimiter','recordFailedAttempt')) {
            RateLimiter::recordFailedAttempt($ip);
        }
        $error = $error ?? 'Invalid credentials.';
    }
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
        button { background: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        button:hover { background: #0069d9; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="POST">
            <?php csrf_field(); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
