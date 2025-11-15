<?php
/**
 * Centralized permission registry for admin access control
 */
class PermissionRegistry {
    private static $permissions = [
        // System permissions
        'system.manage' => 'Full system administration access',
        'system.settings' => 'Manage system settings',
        'system.backups' => 'Manage system backups',
        
        // Content permissions
        'content.create' => 'Create new content',
        'content.edit' => 'Edit existing content',
        'content.delete' => 'Delete content',
        'content.publish' => 'Publish/unpublish content',
        
        // User management
        'users.manage' => 'Manage user accounts',
        'roles.manage' => 'Manage roles and permissions',
        
        // Plugin management
        'plugins.manage' => 'Install/uninstall plugins',
        'plugins.configure' => 'Configure plugin settings',
        
        // Media management
        'media.upload' => 'Upload media files',
        'media.manage' => 'Manage media library',
        
        // API permissions
        'api.manage' => 'Manage API keys and access',
        
        // Worker permissions
        'workers.manage' => 'Manage background workers',
        'workers.monitor' => 'Monitor worker status'
    ];

    private static $roleHierarchy = [
        'super_admin' => [
            'admin' => [
                'editor' => [
                    'author',
                    'contributor'
                ],
                'developer'
            ]
        ]
    ];

    /**
     * Get all registered permissions
     */
    public static function getAllPermissions(): array {
        return self::$permissions;
    }

    /**
     * Check if permission exists
     */
    public static function hasPermission(string $permission): bool {
        return isset(self::$permissions[$permission]);
    }

    /**
     * Get permission description
     */
    public static function getDescription(string $permission): ?string {
        return self::$permissions[$permission] ?? null;
    }

    /**
     * Get role hierarchy
     */
    public static function getRoleHierarchy(): array {
        return self::$roleHierarchy;
    }

    /**
     * Check if role inherits from another role
     */
    public static function roleInherits(string $role, string $parentRole): bool {
        $checkHierarchy = function($roles, $target) use (&$checkHierarchy) {
            foreach ($roles as $key => $value) {
                if (is_array($value)) {
                    if ($key === $target || $checkHierarchy($value, $target)) {
                        return true;
                    }
                } elseif ($value === $target) {
                    return true;
                }
            }
            return false;
        };

        return $checkHierarchy(self::$roleHierarchy, $parentRole);
    }
}
