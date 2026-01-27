<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/permissionmanager.php';
require_once __DIR__ . '/../../core/csrf.php';

// Check admin permissions
if (!PermissionManager::hasPermission('manage_users')) {
    header('Location: /admin/dashboard.php');
    exit;
}

// Get user ID from query string
$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userId <= 0) {
    $_SESSION['error'] = 'Invalid user ID';
    header('Location: /admin/users/');
    exit;
}

// Get tenant ID if in multi-tenant mode
$tenantId = isset($_GET['tenant_id']) ? (int)$_GET['tenant_id'] : null;

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Get all available roles
$allRoles = PermissionManager::getAllRoles($tenantId);
$userRoles = PermissionManager::getUserRoles($userId, $tenantId);

// Get user details
$user = PermissionManager::getUserById($userId);
if (!$user) {
    $_SESSION['error'] = 'User not found';
    header('Location: /admin/users/');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') { csrf_validate_or_403(); }
    header('Location: save.php');
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User Permissions</title>
    <link rel="stylesheet" href="/admin/assets/css/permissions.css">
</head>
<body>
    <div class="container">
        <h1>Edit Permissions for <?= htmlspecialchars($user['name']) ?></h1>
        
        <!-- Navigation Links -->
        <div class="nav-links">
            <a href="/admin/users/" class="btn">Users Panel</a>
            <a href="/admin/roles/" class="btn">Roles Panel</a>
            <a href="/admin/users/edit.php?id=<?= $userId ?>" class="btn">Back to User</a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?= htmlspecialchars($_SESSION['error']) ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success"><?= htmlspecialchars($_SESSION['success']) ?></div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        <form method="post" action="save.php">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            <input type="hidden" name="user_id" value="<?= $userId ?>">
            <?php if ($tenantId !== null): ?>
                <input type="hidden" name="tenant_id" value="<?= $tenantId ?>">
            <?php endif; ?>            <!-- Tenant-scoped Permission Matrix -->
            <div class="permission-matrix">
                <h2>Role Assignments</h2>
                <?php foreach ($allRoles as $role): ?>
                    <div class="role-item">
                        <input type="checkbox" 
                               name="roles[]" 
                               id="role_<?= $role['id'] ?>" 
                               value="<?= $role['id'] ?>"
                               <?= in_array($role['id'], $userRoles) ? 'checked' : '' ?>>
                        <label for="role_<?= $role['id'] ?>">
                            <?= htmlspecialchars($role['name']) 
?>                        </label>
                        <span class="role-description">
                            <?= htmlspecialchars($role['description'] ?? '') 
?>                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn primary">Save Changes</button>
                <a href="/admin/users/edit.php?id=<?= $userId ?>" class="btn">Cancel</a>
            </div>
        </form>
    </div>

    <script src="/admin/assets/js/permissions.js"></script>
</body>
</html>
