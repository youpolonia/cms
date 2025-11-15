<?php

require_once __DIR__ . '/accesslog.php';

class AccessChecker {
    private $db;
    private $roleManager;
    private $permissionManager;
    private $cache = [];

    public function __construct($dbConnection, RoleManager $roleManager, PermissionManager $permissionManager) {
        $this->db = $dbConnection;
        $this->roleManager = $roleManager;
        $this->permissionManager = $permissionManager;
    }

    public function hasPermission(int $userId, string $permissionId, ?int $siteId = null): bool {
        // Check cache first
        $cacheKey = "{$userId}_{$permissionId}_" . ($siteId ?? 'global');
        if (isset($this->cache[$cacheKey])) {
            return $this->cache[$cacheKey];
        }

        // Get all roles for user
        $roles = $this->getUserRoles($userId, $siteId);

        // Check each role for permission
        foreach ($roles as $roleId) {
            $permissions = $this->roleManager->getPermissionsForRole($roleId);
            if (in_array($permissionId, $permissions)) {
                $this->cache[$cacheKey] = true;
                return true;
            }

            // Check permission hierarchy
            foreach ($permissions as $assignedPermission) {
                $childPermissions = $this->permissionManager->getChildPermissions($assignedPermission);
                if (in_array($permissionId, $childPermissions)) {
                    $this->cache[$cacheKey] = true;
                    return true;
                }
            }
        }

        $this->cache[$cacheKey] = false;
        AccessLog::logDeniedAccess($userId, $permissionId, $siteId);
        return false;
    }

    private function getUserRoles(int $userId, ?int $siteId = null): array {
        $sql = "SELECT role_id FROM user_site_roles WHERE user_id = ?";
        $params = [$userId];

        if ($siteId !== null) {
            $sql .= " AND site_id = ?";
            $params[] = $siteId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    public function clearCache(): void {
        $this->cache = [];
    }
}
