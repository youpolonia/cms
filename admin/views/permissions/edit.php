<?php
declare(strict_types=1);

/**
 * Role Assignment Form
 * @var int $userId User ID to assign roles to
 * @var ?int $tenantId Optional tenant ID for tenant-scoped roles
 */
require_once __DIR__ . '/../../../includes/session.php';
require_once __DIR__ . '/../../../includes/permissions.php';

// Check admin permissions
if (!PermissionManager::hasPermission('manage_users')) {
    header('Location: /admin/dashboard.php');
    exit;
}

// Get available roles (combine standard and tenant roles)
$standardRoles = [
    PermissionManager::ROLE_ADMIN,
    PermissionManager::ROLE_EDITOR,
    PermissionManager::ROLE_VIEWER
];

$tenantRoles = [];
if (isset($tenantId)) {
    $tenantRoles = [
        PermissionManager::TENANT_ADMIN,
        PermissionManager::TENANT_EDITOR,
        PermissionManager::TENANT_VIEWER
    ];
}

// Get current user's roles
$currentRoles = [];
$stmt = $db->prepare("SELECT role_id FROM user_roles WHERE user_id = ? AND (tenant_id IS NULL OR tenant_id = ?)");
$stmt->execute([$userId, $tenantId ?? null]);
$currentRoles = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Role Assignment</title>
    <link rel="stylesheet" href="/admin/assets/css/permissions.css">
</head>
<body>
    <h1>Role Assignment</h1>
    <form method="post" action="/admin/permissions/save.php">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars((string)$userId) ?>">
        <?php if (isset($tenantId)): ?>
            <input type="hidden" name="tenant_id" value="<?= htmlspecialchars((string)$tenantId) ?>">
        <?php endif;  ?>
        <div class="role-section">
            <h2>Standard Roles</h2>
            <?php foreach ($standardRoles as $role): ?>                <?php $roleId = PermissionManager::getRoleId($role); 
?>                <div class="role-option">
                    <input type="checkbox" name="roles[]" id="role_<?= $roleId ?>" 
                           value="<?= $roleId ?>" <?= in_array($roleId, $currentRoles) ? 'checked' : '' ?>>
                    <label for="role_<?= $roleId ?>"><?= htmlspecialchars($role) ?></label>
                    <div class="permission-list">
                        <?php foreach (PermissionManager::getPermissionsForRoleByName($role) as $perm): ?>
                            <span class="permission"><?= htmlspecialchars($perm) ?></span>
                        <?php endforeach;  ?>
                    </div>
                </div>
            <?php endforeach;  ?>
        </div>

        <?php if (!empty($tenantRoles)): ?>
            <div class="role-section">
                <h2>Tenant Roles</h2>
                <?php foreach ($tenantRoles as $role): ?>                    <?php $roleId = PermissionManager::getRoleId($role, $tenantId); 
?>                    <div class="role-option">
                        <input type="checkbox" name="roles[]" id="role_<?= $roleId ?>" 
                               value="<?= $roleId ?>" <?= in_array($roleId, $currentRoles) ? 'checked' : '' ?>>
                        <label for="role_<?= $roleId ?>"><?= htmlspecialchars($role) ?></label>
                        <div class="permission-list">
                            <?php foreach (PermissionManager::getPermissionsForRoleByName($role) as $perm): ?>
                                <span class="permission"><?= htmlspecialchars($perm) ?></span>
                            <?php endforeach;  ?>
                        </div>
                    </div>
                <?php endforeach;  ?>
            </div>
        <?php endif;  ?>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Save Changes</button>
            <a href="/admin/users/edit.php?id=<?= $userId ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</body>
</html>
