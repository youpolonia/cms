<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

// POST-only guard
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    header("Location: " . APP_URL . "/admin/users/");
    exit;
}

// CSRF validation
csrf_validate_or_403();

try {
    // Extract user ID from path
    $requestUri = $_SERVER['REQUEST_URI'];
    $pathParts = explode('/', trim(parse_url($requestUri, PHP_URL_PATH), '/'));

    // Find 'reset_process' in path and get next segment as ID
    $userId = null;
    foreach ($pathParts as $i => $part) {
        if ($part === 'reset_process' && isset($pathParts[$i + 1])) {
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

    // Cannot reset own password here
    if ($userId === $currentUserId) {
        $_SESSION['error'] = 'Please use the profile page to change your own password.';
        header("Location: " . APP_URL . "/admin/profile/");
        exit;
    }

    // Get form data
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    // Validate password
    if (empty($newPassword)) {
        $_SESSION['error'] = 'New password is required.';
        header("Location: " . APP_URL . "/admin/users/reset/" . $userId);
        exit;
    }

    if (strlen($newPassword) < 8) {
        $_SESSION['error'] = 'Password must be at least 8 characters long.';
        header("Location: " . APP_URL . "/admin/users/reset/" . $userId);
        exit;
    }

    if ($newPassword !== $confirmPassword) {
        $_SESSION['error'] = 'Passwords do not match.';
        header("Location: " . APP_URL . "/admin/users/reset/" . $userId);
        exit;
    }

    // Additional validation if InputValidator is available
    if (class_exists('InputValidator')) {
        $validation = InputValidator::validatePassword($newPassword);
        if (!$validation['valid']) {
            $_SESSION['error'] = $validation['message'] ?? 'Invalid password.';
            header("Location: " . APP_URL . "/admin/users/reset/" . $userId);
            exit;
        }
    }

    // Database connection
    $db = \core\Database::connection();

    // Load user record
    $stmt = $db->prepare("SELECT id, email, username, role FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        header("Location: " . APP_URL . "/admin/users/");
        exit;
    }

    // Get current user's role for RBAC check
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

    // Begin transaction
    $db->beginTransaction();

    try {
        // Delete any existing password reset tokens for this user
        $stmt = $db->prepare("DELETE FROM user_password_resets WHERE user_id = ?");
        $stmt->execute([$userId]);

        // Hash the new password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update user password
        $stmt = $db->prepare("
            UPDATE users
            SET password_hash = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$passwordHash, $userId]);

        // Commit transaction
        $db->commit();

        require_once CMS_ROOT . '/includes/loggers/user_activity_logger.php';
        UserActivityLogger::log('user.password_reset_admin', ['target_user_id' => $userId]);

        $_SESSION['success'] = 'Password reset successfully.';
        header("Location: " . APP_URL . "/admin/users/edit/" . $userId);
        exit;

    } catch (Throwable $e) {
        $db->rollBack();
        throw $e;
    }

} catch (Throwable $e) {
    error_log("Password reset failed: " . $e->getMessage());
    $_SESSION['error'] = "An unexpected error occurred.";
    header("Location: " . APP_URL . "/admin/users/");
    exit;
}
