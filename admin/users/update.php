<?php
define('CMS_ROOT', dirname(__DIR__, 2));
require_once CMS_ROOT . '/config.php';
require_once CMS_ROOT . '/core/session_boot.php';
cms_session_start('admin');
require_once CMS_ROOT . '/core/csrf.php';
csrf_boot('admin');
require_once CMS_ROOT . '/core/auth.php';
authenticateAdmin();
require_once CMS_ROOT . '/core/security/inputvalidator.php';

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
$username = \Core\Security\InputValidator::sanitizeText($_POST['username'] ?? '');
$email = \Core\Security\InputValidator::sanitizeText($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$role = \Core\Security\InputValidator::sanitizeText($_POST['role'] ?? '');
$tenants = $_POST['tenants'] ?? [];
$permissions = $_POST['permissions'] ?? [];

// Validate ID
if ($id <= 0) {
    $errors[] = 'Invalid user ID';
}

// Check if user exists
if (empty($errors)) {
    $db = \core\Database::connection();
    $stmt = $db->prepare("SELECT id, role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$existingUser) {
        $errors[] = 'User not found';
    }
}

// Validate username
if (!\Core\Security\InputValidator::validateUsername($username)) {
    $errors[] = 'Username must be 3-20 chars (letters, numbers, underscore)';
}

// Validate email
if (!\Core\Security\InputValidator::validateEmail($email)) {
    $errors[] = 'Invalid email format';
}

// Validate password if provided
if (!empty($password)) {
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }

    if (!\Core\Security\InputValidator::validatePassword($password)) {
        $errors[] = 'Password must be at least 8 chars with 1 letter and 1 number';
    }
}

// Validate role
$validRoles = ['user', 'editor', 'admin', 'superadmin'];
if (!in_array($role, $validRoles, true)) {
    $errors[] = 'Invalid role selected';
}

// Security rule: Cannot modify own role unless superadmin
$currentUserId = $_SESSION['user_id'] ?? 0;
$currentUserRole = $_SESSION['role'] ?? '';

if ($id === $currentUserId && isset($existingUser) && $existingUser['role'] !== $role) {
    if ($currentUserRole !== 'superadmin') {
        $errors[] = 'You cannot modify your own role';
    }
}

// Validate tenants array
if (!is_array($tenants)) {
    $errors[] = 'Invalid tenants data';
} else {
    $tenants = array_filter(array_map('intval', $tenants), function($t) {
        return $t > 0;
    });
}

// Validate permissions array
if (!is_array($permissions)) {
    $errors[] = 'Invalid permissions data';
} else {
    $permissions = array_filter(array_map(function($p) {
        return \Core\Security\InputValidator::sanitizeText($p);
    }, $permissions));
}

// If validation errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['error'] = implode('<br>', $errors);
    header('Location: edit.php?id=' . $id);
    exit;
}

// Update user
try {
    $db = \core\Database::connection();

    // Start transaction
    $db->beginTransaction();

    // Prepare update data
    $updateFields = [
        'username' => $username,
        'email' => $email,
        'role' => $role
    ];

    // Add password if provided
    if (!empty($password)) {
        $updateFields['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
    }

    // Build update query
    $setClause = [];
    $params = [];
    foreach ($updateFields as $field => $value) {
        $setClause[] = "$field = ?";
        $params[] = $value;
    }
    $params[] = $id; // WHERE id = ?

    $sql = "UPDATE users SET " . implode(', ', $setClause) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    // Update tenants (assuming user_tenants table exists)
    $stmt = $db->prepare("DELETE FROM user_tenants WHERE user_id = ?");
    $stmt->execute([$id]);

    if (!empty($tenants)) {
        $stmt = $db->prepare("INSERT INTO user_tenants (user_id, tenant_id) VALUES (?, ?)");
        foreach ($tenants as $tenantId) {
            $stmt->execute([$id, $tenantId]);
        }
    }

    // Update permissions (assuming user_permissions table exists)
    $stmt = $db->prepare("DELETE FROM user_permissions WHERE user_id = ?");
    $stmt->execute([$id]);

    if (!empty($permissions)) {
        $stmt = $db->prepare("INSERT INTO user_permissions (user_id, permission) VALUES (?, ?)");
        foreach ($permissions as $permission) {
            $stmt->execute([$id, $permission]);
        }
    }

    // Commit transaction
    $db->commit();

    require_once CMS_ROOT . '/includes/loggers/user_activity_logger.php';
    UserActivityLogger::log('user.update_admin', ['target_user_id' => $id]);

    $_SESSION['success'] = 'User updated successfully';
    header('Location: index.php');
    exit;

} catch (Throwable $e) {
    // Rollback on error
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }

    error_log('User update failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Failed to update user. Please try again.';
    header('Location: edit.php?id=' . $id);
    exit;
}
