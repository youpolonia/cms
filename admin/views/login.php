<?php
require_once __DIR__ . '/../../core/csrf.php';
// Start session for CSRF token

// Include InputValidator and AuthService
require_once __DIR__ . '/../../includes/security/InputValidator.php';
require_once __DIR__ . '/../../includes/security/authservice.php';

// Initialize variables
$errors = [];
$username = '';
$password = '';
$tenantId = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_validate_or_403();
    // Validate CSRF token
    if (empty($_POST['csrf_token']) ||
        $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '') ||
        ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SESSION['init_ip']) &&
         $_SESSION['init_ip'] !== $_SERVER['REMOTE_ADDR'])) {
        $errors[] = 'Invalid security token or IP mismatch';
    } else {
        // Validate inputs
        $username = InputValidator::sanitizeString($_POST['username'] ?? '');
        $password = InputValidator::sanitizeString($_POST['password'] ?? '');
        $tenantId = (int)($_POST['tenant_id'] ?? 0);

        if (empty($username)) {
            $errors[] = 'Username/Email is required';
        } elseif (strpos($username, '@') !== false && !InputValidator::validateEmail($username)) {
            $errors[] = 'Invalid email format';
        }

        if (empty($password)) {
            $errors[] = 'Password is required';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }

        // If no errors, proceed with login
        if (empty($errors)) {
            // Initialize PDO (should be moved to bootstrap)
            require_once __DIR__ . '/../../core/database.php';
            $pdo = \core\Database::connection();
            $authService = new AuthService($pdo);

            $user = $authService->authenticate($username, $password);
            
            if ($user && AuthService::validateTenant($user['id'], $tenantId)) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['tenant_id'] = $tenantId;
                $_SESSION['init_ip'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['last_login'] = date('Y-m-d H:i:s');
                header('Location: dashboard.php');
                exit;
            } else {
                $errors[] = 'Invalid credentials or tenant access';
            }
        }
    }
}

// Generate new CSRF token for form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="/admin/assets/css/login.css">
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach;  ?>
            </div>
        <?php endif;  ?>
        <form method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="username">Username or Email</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <label for="tenant_id">Tenant ID</label>
                <input type="number" id="tenant_id" name="tenant_id" required>
            </div>
            
            
            <button type="submit" class="btn-login">Login</button>
        </form>
    </div>
</body>
</html>