<?php
use Services\PathResolver;
require_once __DIR__ . '/../includes/database/connection.php';
require_once __DIR__ . '/../includes/core/auth.php';
require_once __DIR__.'/../includes/auth/RateLimiter.php';

// Start session if not already started
require_once PathResolver::core('session.php');

$errors = [];
$db = \core\Database::connection();
$rateLimiter = new Includes\Auth\RateLimiter($db);
$rateLimitKey = 'login:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        $errors[] = 'Invalid CSRF token';
    }
    
    // Check rate limit
    if (empty($errors) && $rateLimiter->tooManyAttempts($rateLimitKey)) {
        http_response_code(429);
        header('Retry-After: ' . $rateLimiter->availableIn($rateLimitKey));
        die('Too many login attempts. Please try again later.');
    }

    // Validate inputs
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($password)) $errors[] = 'Password is required';

    if (empty($errors)) {
        try {
            // Get user by username
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                // Clear rate limit on successful login
                $rateLimiter->clear($rateLimitKey);
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['is_admin'] ? 'admin' : 'user';
                $_SESSION['tenant_id'] = $user['tenant_id'];
                
                // Regenerate session ID for security
                session_regenerate_id(true);
                
                // Redirect to dashboard
                header('Location: /dashboard.php');
                exit;
            } else {
                $errors[] = 'Invalid username or password';
                // Increment rate limit on failed attempt
                $rateLimiter->hit($rateLimitKey);
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Login failed';
        }
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Login</h1>
    <?php foreach ($errors as $error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endforeach; ?>
<form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <div>
            <label>Username:</label>
            <input type="text" name="username"
 required>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password"
 required>
        </div>
        <button type="submit">Login</button>
    </form>
</body>
</html>
