<?php
require_once __DIR__ . '/../core/bootstrap.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
require_once __DIR__ . '/../core/csrf.php';

// Start session and initialize CSRF
csrf_boot('public');

// Load credentials
$config = require __DIR__ . '/../config/credentials.php';

// Check if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header('Location: /admin/dashboard.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token using standardized function
    csrf_validate_or_403();

    // Validate credentials
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username and password are required';
    } elseif ($username === $config['admin']['username']
        && password_verify($password, $config['admin']['password_hash'])) {
        // Successful login - preserve CSRF token during session regeneration
        $csrfToken = $_SESSION['csrf_token'] ?? null;
        session_regenerate_id(true);
        // Restore CSRF token after regeneration
        if ($csrfToken) {
            $_SESSION['csrf_token'] = $csrfToken;
        }
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        header('Location: /admin/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
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
        .login-form { max-width: 400px; margin: 100px auto; padding: 20px; background: white; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 10px 15px; cursor: pointer; }
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Admin Login</h2>
        
<?php if (!empty($_SESSION['error'])): ?>
            <div class="error">
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php elseif (!empty($error)): ?>
            <div class="error">
                <?= htmlspecialchars($error) ?>
            </div>
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
