<?php
declare(strict_types=1);

/**
 * Database-backed Permission Management
 */
class PermissionManager {
    // Role constants
    public const ROLE_ADMIN = 'Admin';
    public const ROLE_EDITOR = 'Editor';
    public const ROLE_VIEWER = 'Viewer';
    
    // Tenant-scoped role constants
    public const TENANT_ADMIN = 'TenantAdmin';
    public const TENANT_EDITOR = 'TenantEditor';
    public const TENANT_VIEWER = 'TenantViewer';

    // Permission map configuration
    private const PERMISSION_MAP = [
        self::ROLE_ADMIN => [
            'manage_users',
            'manage_content',
            'manage_settings',
            'view_reports'
        ],
        self::ROLE_EDITOR => [
            'manage_content',
            'view_reports'
        ],
        self::ROLE_VIEWER => [
            'view_content',
            'view_reports'
        ],
        self::TENANT_ADMIN => [
            'manage_tenant_users',
            'manage_tenant_content',
            'view_tenant_reports'
        ],
        self::TENANT_EDITOR => [
            'manage_tenant_content',
            'view_tenant_reports'
        ],
        self::TENANT_VIEWER => [
            'view_tenant_content',
            'view_tenant_reports'
        ]
    ];
    /**
     * Assign role to user
     * @param int $userId User ID
     * @param int $roleId Role ID
     * @return bool True on success
     */
    public static function assignRoleToUser(int $userId, int $roleId, ?int $tenantId = null): bool {
        global $db;
        
        if ($tenantId !== null) {
            TenantValidator::validate($tenantId);
            $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id, tenant_id) VALUES (?, ?, ?)");
            $result = $stmt->execute([$userId, $roleId, $tenantId]);
        } else {
            $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
            $result = $stmt->execute([$userId, $roleId]);
        }
        
        if ($result) {
            self::invalidatePermissionCache($userId);
        }
        return $result;
    }

    public static function assignPermissionToRole(int $roleId, int $permissionId, ?int $tenantId = null): bool {
        global $db;
        
        if ($tenantId !== null) {
            TenantValidator::validate($tenantId);
            $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id, tenant_id) VALUES (?, ?, ?)");
            $result = $stmt->execute([$roleId, $permissionId, $tenantId]);
        } else {
            $stmt = $db->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            $result = $stmt->execute([$roleId, $permissionId]);
        }
        
        if ($result) {
            self::invalidateRoleCache($roleId);
        }
        return $result;
    }

    public static function getChildRoles(int $roleId, ?int $tenantId = null): array {
        global $db;
        
        $sql = "SELECT id FROM roles WHERE parent_id = ?";
        $params = [$roleId];
        
        if ($tenantId !== null) {
            TenantValidator::validate($tenantId);
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public static function userHasPermission(int $userId, string $permission, ?int $tenantId = null): bool {
        global $db;
        
        // Get user's roles
        $sql = "SELECT role_id FROM user_roles WHERE user_id = ?";
        $params = [$userId];
        
        if ($tenantId !== null) {
            TenantValidator::validate($tenantId);
            $sql .= " AND (tenant_id IS NULL OR tenant_id = ?)";
            $params[] = $tenantId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $roleIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Check each role for permission (including inherited)
        foreach ($roleIds as $roleId) {
            if (self::roleHasPermission($roleId, $permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Middleware-style permission check
     * @param int $userId
     * @param string $permission
     * @throws PermissionDeniedException
     */
    public static function checkPermission(int $userId, string $permission, ?int $tenantId = null): void {
        if (!self::userHasPermission($userId, $permission, $tenantId)) {
            $scope = $tenantId ? "for tenant $tenantId" : "";
            throw new PermissionDeniedException("User does not have required permission: $permission $scope");
        }
    }

    /**
     * Helper function for view/template permission checks
     * @param string $permission
     * @return bool
     */
    public static function hasPermission(string $permission, ?int $tenantId = null): bool {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        // Check if permissions are cached in session
        $cacheKey = 'user_permissions_' . $_SESSION['user_id'];
        if (isset($_SESSION[$cacheKey]) && is_array($_SESSION[$cacheKey])) {
            return in_array($permission, $_SESSION[$cacheKey]);
        }
        
        // Not cached - check and cache
        $hasPerm = self::userHasPermission((int)$_SESSION['user_id'], $permission, $tenantId);
        if ($hasPerm) {
            if (!isset($_SESSION[$cacheKey])) {
                $_SESSION[$cacheKey] = [];
            }
            $_SESSION[$cacheKey][] = $permission;
        }
        return $hasPerm;
    }

    /**
     * Invalidate permission cache for a user
     * @param int $userId
     */
    private static function invalidatePermissionCache(int $userId): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $cacheKey = 'user_permissions_' . $userId;
            unset($_SESSION[$cacheKey]);
        }
    }

    /**
     * Invalidate cache for all users with a role
     * @param int $roleId
     */
    private static function invalidateRoleCache(int $roleId): void {
        global $db;
        
        $stmt = $db->prepare("SELECT user_id FROM user_roles WHERE role_id = ?");
        $stmt->execute([$roleId]);
        $userIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        
        foreach ($userIds as $userId) {
            self::invalidatePermissionCache($userId);
        }
    }

    public static function getPermissionsForRole(int $roleId): array {
        global $db;
        
        // Get direct permissions
        $stmt = $db->prepare(
            "SELECT p.name FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = ?"
        );
        $stmt->execute([$roleId]);
        $permissions = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        // Get inherited permissions from parent roles
        $childRoles = self::getChildRoles($roleId);
        foreach ($childRoles as $childRoleId) {
            $permissions = array_merge($permissions, self::getPermissionsForRole($childRoleId));
        }

        return array_unique($permissions);
    }

    public static function roleHasPermission(int $roleId, $permission): bool {
        global $db;
        
        if (is_int($permission)) {
            $stmt = $db->prepare(
                "SELECT 1 FROM role_permissions
                 WHERE role_id = ? AND permission_id = ?"
            );
            return $stmt->execute([$roleId, $permission]) && (bool)$stmt->fetch();
        }
        
        if (is_string($permission)) {
            $stmt = $db->prepare(
                "SELECT 1 FROM permissions p
                 JOIN role_permissions rp ON p.id = rp.permission_id
                 WHERE rp.role_id = ? AND p.name = ?"
            );
            return $stmt->execute([$roleId, $permission]) && (bool)$stmt->fetch();
        }
        
        throw new InvalidArgumentException("Permission must be string or integer");
    }

    public static function getRoleId(string $roleName, ?int $tenantId = null): ?int {
        global $db;
        
        $sql = "SELECT id FROM roles WHERE name = ?";
        $params = [$roleName];
        
        if ($tenantId !== null) {
            TenantValidator::validate($tenantId);
            $sql .= " AND tenant_id = ?";
            $params[] = $tenantId;
        }
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($params) ? $stmt->fetchColumn() : null;
    }

    public static function getPermissionId(string $permissionName, ?int $tenantId = null): ?int {
        global $db;
        
        $sql = "SELECT id FROM permissions WHERE name = ?";
        $params = [$permissionName];
        
        if ($tenantId !== null) {
            TenantValidator::validate($tenantId);
            $sql .= " AND (tenant_id IS NULL OR tenant_id = ?)";
            $params[] = $tenantId;
        }
        
        $stmt = $db->prepare($sql);
        return $stmt->execute($params) ? $stmt->fetchColumn() : null;
    }

    public static function roleHasPermissionByName(string $roleName, string $permissionName): bool {
        $roleId = self::getRoleId($roleName);
        $permissionId = self::getPermissionId($permissionName);
        return $roleId && $permissionId
            ? self::roleHasPermission($roleId, $permissionName)
            : false;
    }

    public static function getPermissionsForRoleByName(string $roleName): array {
        $roleId = self::getRoleId($roleName);
        return $roleId ? self::getPermissionsForRole($roleId) : [];
    }
}
