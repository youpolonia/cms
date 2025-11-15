<?php
require_once __DIR__ . '/../config.php';
if (!defined('DEV_MODE') || DEV_MODE !== true) { http_response_code(403); exit; }
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';
require_once __DIR__ . '/../core/auth.php';
csrf_boot('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    $u = isset($_POST['username']) ? (string)$_POST['username'] : '';
    $p = isset($_POST['password']) ? (string)$_POST['password'] : '';
    [$ok, $user] = authenticateAdmin($u, $p, 'admins');
    if ($ok) {
        // Preserve CSRF token before regenerating session
        $csrfToken = $_SESSION['csrf_token'] ?? null;
        session_regenerate_id(true);
        // Restore CSRF token after regeneration
        if ($csrfToken) {
            $_SESSION['csrf_token'] = $csrfToken;
        }
        $_SESSION['admin_authenticated'] = true;
        $_SESSION['admin_username'] = $user['username'] ?? $u;
        $_SESSION['admin_id'] = $user['id'] ?? $user['admin_id'] ?? null;
        $_SESSION['admin_user_id'] = $user['id'] ?? null;
        header('Location: /admin/index.php', true, 303);
        exit;
    }
    echo 'login failed';
    exit;
}
?><!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/admin/assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF'] ?? '/admin/login.php', ENT_QUOTES, 'UTF-8') ?>">
            <?php csrf_field(); ?>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html>
