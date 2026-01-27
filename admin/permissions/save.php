<?php
declare(strict_types=1);
require_once __DIR__ . '/../core/csrf.php';

csrf_boot('admin');

require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/permissions.php';
require_once __DIR__ . '/permissionmanager.php';
require_once __DIR__ . '/../../core/cache/SessionCacheAdapter.php';

// Check admin permissions
if (!PermissionManager::hasPermission('manage_users')) {
    header('Location: /admin/dashboard.php');
    exit;
}

csrf_validate_or_403();

// Validate required fields
if (!isset($_POST['user_id']) || !is_numeric($_POST['user_id'])) {
    $_SESSION['error'] = 'Invalid user ID';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$userId = (int)$_POST['user_id'];
$tenantId = isset($_POST['tenant_id']) ? (int)$_POST['tenant_id'] : null;
$selectedRoles = $_POST['roles'] ?? [];

try {
    // Begin transaction
    $db->beginTransaction();

    // First remove all existing roles for this user (within current tenant scope)
    $deleteSql = "DELETE FROM user_roles WHERE user_id = ?";
    $deleteParams = [$userId];
    
    if ($tenantId !== null) {
        $deleteSql .= " AND (tenant_id IS NULL OR tenant_id = ?)";
        $deleteParams[] = $tenantId;
    }
    
    $stmt = $db->prepare($deleteSql);
    $stmt->execute($deleteParams);

    // Add newly selected roles
    foreach ($selectedRoles as $roleId) {
        if (!is_numeric($roleId)) {
            continue;
        }
        
        $roleId = (int)$roleId;
        if (!PermissionManager::assignRoleToUser($userId, $roleId, $tenantId)) {
            throw new RuntimeException("Failed to assign role $roleId to user $userId");
        }
    }

    // Commit transaction
    $db->commit();

    $_SESSION['success'] = 'Role assignments updated successfully';
    header('Location: /admin/users/edit.php?id=' . $userId);
    exit;
} catch (Exception $e) {
    // Rollback on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    error_log('Permission update failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Failed to update role assignments';
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
