<?php
/**
 * Access Control Functions for CMS RBAC System
 * Provides permission checking with role hierarchy support
 */

declare(strict_types=1);

require_once __DIR__ . '/permissionmanager.php';

class Access {
    /**
     * Check if role has permission (backward compatible)
     * @param string|int $role Role name or ID
     * @param string|int $permission Permission name or ID
     * @return bool True if role has permission
     * @throws InvalidArgumentException If invalid role or permission
     */
    public static function roleHasPermission($role, $permission): bool {
        if (is_string($role) && is_string($permission)) {
            // Legacy string-based check
            return PermissionManager::roleHasPermissionByName($role, $permission);
        }
        
        if (is_int($role) && (is_int($permission) || is_string($permission))) {
            // New ID-based check
            return PermissionManager::roleHasPermission($role, $permission);
        }

        throw new InvalidArgumentException("Invalid role/permission combination");
    }

    /**
     * Check if user has permission (via their role)
     * @param int $userId User ID
     * @param string|int $permission Permission name or ID
     * @return bool True if user has permission
     */
    public static function userHasPermission(int $userId, $permission): bool {
        return PermissionManager::userHasPermission($userId, $permission);
    }

    /**
     * Get all permissions for a role (backward compatible)
     * @param string|int $role Role name or ID
     * @return array<string> List of permissions
     */
    public static function getPermissionsForRole($role): array {
        if (is_string($role)) {
            // Legacy string-based lookup
            return PermissionManager::getPermissionsForRoleByName($role);
        }
        
        if (is_int($role)) {
            // New ID-based lookup
            return PermissionManager::getPermissionsForRole($role);
        }

        throw new InvalidArgumentException("Invalid role type");
    }

    /**
     * Check if role has permission by name (legacy support)
     */
    private static function roleHasPermissionByName(string $role, string $permission): bool {
        // Implementation for backward compatibility
        $roleId = PermissionManager::getRoleId($role);
        $permissionId = PermissionManager::getPermissionId($permission);
        return $roleId && $permissionId
            ? PermissionManager::roleHasPermission($roleId, $permissionId)
            : false;
    }
}
