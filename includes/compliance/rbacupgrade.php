<?php
declare(strict_types=1);

/**
 * Compliance - RBAC Upgrade
 * Enhanced role-based access control system
 */
class RBACUpgrade {
    private static array $roles = [];
    private static array $permissions = [];
    private static string $logFile = __DIR__ . '/../../logs/rbac_audit.log';

    /**
     * Define a new role with permissions
     */
    public static function defineRole(
        string $roleName,
        array $permissions,
        ?string $parentRole = null
    ): void {
        self::$roles[$roleName] = [
            'permissions' => $permissions,
            'parent' => $parentRole,
            'created_at' => time()
        ];

        foreach ($permissions as $permission) {
            if (!isset(self::$permissions[$permission])) {
                self::$permissions[$permission] = [];
            }
            self::$permissions[$permission][] = $roleName;
        }

        self::logEvent("Role defined: $roleName");
    }

    /**
     * Check if a role has permission
     */
    public static function hasPermission(
        string $roleName,
        string $permission
    ): bool {
        if (!isset(self::$roles[$roleName])) {
            return false;
        }

        // Check direct permissions
        if (in_array($permission, self::$roles[$roleName]['permissions'])) {
            return true;
        }

        // Check inherited permissions
        $parentRole = self::$roles[$roleName]['parent'];
        if ($parentRole !== null) {
            return self::hasPermission($parentRole, $permission);
        }

        return false;
    }

    /**
     * Validate all role permissions
     */
    public static function validatePermissions(): array {
        $errors = [];
        foreach (self::$roles as $role => $data) {
            if ($data['parent'] && !isset(self::$roles[$data['parent']])) {
                $errors[] = "Role $role has invalid parent: {$data['parent']}";
            }
        }
        return $errors;
    }

    /**
     * Get all permissions for a role (including inherited)
     */
    public static function getAllPermissions(string $roleName): array {
        if (!isset(self::$roles[$roleName])) {
            return [];
        }

        $permissions = self::$roles[$roleName]['permissions'];
        $parentRole = self::$roles[$roleName]['parent'];

        if ($parentRole !== null) {
            $parentPermissions = self::getAllPermissions($parentRole);
            $permissions = array_unique(array_merge($permissions, $parentPermissions));
        }

        return $permissions;
    }

    private static function logEvent(string $message): void {
        file_put_contents(
            self::$logFile,
            date('Y-m-d H:i:s') . " - $message\n",
            FILE_APPEND
        );
    }

    // BREAKPOINT: Continue with context-aware permission checks
}
