<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

$pageTitle = 'Reset Password';

try {
    // Extract user ID from path
    $requestUri = $_SERVER['REQUEST_URI'];
    $pathParts = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));

    // Find 'reset' in path and get next segment as ID
    $userId = null;
    foreach ($pathParts as $i => $part) {
        if ($part === 'reset' && isset($pathParts[$i + 1])) {
            $userId = (int)$pathParts[$i + 1];
            break;
        }
    }

    // Validate user ID
    if (!$userId || $userId <= 0) {
        $_SESSION['error'] = 'Invalid user ID.';
        header("Location: " . APP_URL . "/admin/users/");
        exit;
    }

    // Get current admin user
    $currentUserId = $_SESSION['user_id'] ?? 0;

    // Cannot reset own password here - redirect to profile
    if ($userId === $currentUserId) {
        $_SESSION['error'] = 'Please use the profile page to change your own password.';
        header("Location: " . APP_URL . "/admin/profile/");
        exit;
    }

    // Load user record
    $db = \core\Database::connection();
    $stmt = $db->prepare("SELECT id, email, username, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        header("Location: " . APP_URL . "/admin/users/");
        exit;
    }

    // Get current user's role
    $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$currentUserId]);
    $currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$currentUser) {
        $_SESSION['error'] = 'Current user not found.';
        header("Location: " . APP_URL . "/admin/users/");
        exit;
    }

    // RBAC: Cannot reset superadmin password unless current user is superadmin
    if ($user['role'] === 'superadmin' && $currentUser['role'] !== 'superadmin') {
        $_SESSION['error'] = 'You do not have permission to reset a superadmin password.';
        header("Location: " . APP_URL . "/admin/users/");
        exit;
    }

    // Retrieve messages
    $error = $_SESSION['error'] ?? '';
    $success = $_SESSION['success'] ?? '';
    unset($_SESSION['error'], $_SESSION['success']);

} catch (Throwable $e) {
    error_log("Password reset form failed: " . $e->getMessage());
    $_SESSION['error'] = "An unexpected error occurred.";
    header("Location: " . APP_URL . "/admin/users/");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - Admin</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/admin/css/admin-ui.css">
</head>
<body>
    <?php require_once CMS_ROOT . '/admin/includes/header.php'; ?>
    <?php require_once CMS_ROOT . '/admin/includes/navigation.php'; ?>

    <main class="admin-content">
        <div class="container">
            <h1><?= htmlspecialchars($pageTitle) ?></h1>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>Reset Password for <?= htmlspecialchars($user['username']) ?></h2>
                    <p class="text-muted">Email: <?= htmlspecialchars($user['email']) ?></p>
                    <p class="text-muted">Role: <?= htmlspecialchars($user['role']) ?></p>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= APP_URL ?>/admin/users/reset_process/<?= $user['id'] ?>">
                        <?php csrf_field('admin'); ?>

                        <div class="form-group">
                            <label for="new_password">New Password *</label>
                            <input
                                type="password"
                                id="new_password"
                                name="new_password"
                                class="form-control"
                                required
                                minlength="8"
                            >
                            <small class="form-text">Minimum 8 characters</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password *</label>
                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-control"
                                required
                                minlength="8"
                            >
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                            <a href="<?= APP_URL ?>/admin/users/edit/<?= $user['id'] ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
