<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();

// Enforce POST-only
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Method Not Allowed']);
    } else {
        header('Location: index.php');
    }
    exit;
}

// CSRF validation BEFORE any state change
csrf_validate_or_403();

// Validate inputs
$errors = [];
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Validate ID
if ($id <= 0) {
    $errors[] = 'Invalid user ID';
}

// Get current user info for security checks
$currentUserId = $_SESSION['user_id'] ?? 0;
$currentUserRole = $_SESSION['role'] ?? '';

// Check if user exists and get their role
$existingUser = null;
if (empty($errors)) {
    $db = \core\Database::connection();
    $stmt = $db->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingUser) {
        $errors[] = 'User not found';
    }
}

// Security lock: Cannot delete yourself
if ($id === $currentUserId) {
    $errors[] = 'You cannot delete your own account';
}

// Security lock: Cannot delete superadmin unless you are superadmin
if ($existingUser && $existingUser['role'] === 'superadmin' && $currentUserRole !== 'superadmin') {
    $errors[] = 'Only superadmins can delete superadmin accounts';
}

// If validation errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: index.php');
    exit;
}

// Delete user
try {
    $db = \core\Database::connection();

    // Start transaction
    $db->beginTransaction();

    // Delete related records from user_permissions
    $stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$id]);

    // Delete related records from user_tenants
    $stmt = $db->prepare("DELETE FROM user_tenants WHERE user_id = ?");
    $stmt->execute([$id]);

    // Delete main user record
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    // Commit transaction
    $db->commit();

    require_once CMS_ROOT . '/includes/loggers/user_activity_logger.php';
    UserActivityLogger::log('user.delete', ['target_user_id' => $id]);

    $_SESSION['success'] = 'User deleted successfully';
    header('Location: index.php');
    exit;

} catch (Throwable $e) {
    // Rollback on error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log('User delete failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Failed to delete user. Please try again.';
    header('Location: index.php');
    exit;
}
