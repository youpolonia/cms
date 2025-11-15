<?php
require_once __DIR__ . '/../includes/database/connection.php';
require_once __DIR__ . '/../includes/core/auth.php';
require_once __DIR__.'/../includes/auth/RateLimiter.php';

// Start session if not already started
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../core/session_boot.php';
cms_session_start('public');

$errors = [];
$db = \core\Database::connection();
$rateLimiter = new Includes\Auth\RateLimiter($db);
$rateLimitKey = 'register:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check rate limit
    if ($rateLimiter->tooManyAttempts($rateLimitKey)) {
        http_response_code(429);
        header('Retry-After: ' . $rateLimiter->availableIn($rateLimitKey));
        die('Too many registration attempts. Please try again later.');
    }
    // Validate inputs
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $tenantId = bin2hex(random_bytes(16)); // Generate tenant ID

    // Validation
    if (empty($username)) $errors[] = 'Username is required';
    if (empty($email)) $errors[] = 'Email is required';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
    if (empty($password)) $errors[] = 'Password is required';
    if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';

    if (empty($errors)) {
        try {
            // Check if username/email exists
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $errors[] = 'Username or email already exists';
            } else {
                // Create user
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users 
                    (username, email, password_hash, first_name, last_name, tenant_id, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $username, 
                    $email, 
                    $passwordHash,
                    $firstName,
                    $lastName,
                    $tenantId
                ]);

                // Set session and redirect
                $_SESSION['user_id'] = $db->lastInsertId();
                $_SESSION['user_role'] = 'admin'; // First user is admin
                $_SESSION['tenant_id'] = $tenantId;
                // Clear rate limit on successful registration
                $rateLimiter->clear($rateLimitKey);
                header('Location: /dashboard.php');
                exit;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $errors[] = 'Registration failed';
            // Increment rate limit on failed attempt
            $rateLimiter->hit($rateLimitKey);
        }
    }
}
?><!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Register</h1>
    <?php foreach ($errors as $error): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endforeach; ?>
<form method="post">
<?= csrf_field(); ?>
        <div>
            <label>Username:</label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Email:</label>
            <input type="email" name="email" required>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <label>Confirm Password:</label>
            <input type="password" name="confirm_password" required>
        </div>
        <div>
            <label>First Name:</label>
            <input type="text" name="first_name">
        </div>
        <div>
            <label>Last Name:</label>
            <input type="text" name="last_name">
        </div>
        <button type="submit">Register</button>
    </form>
</body>
</html>