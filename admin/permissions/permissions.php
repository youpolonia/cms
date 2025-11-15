<?php
/**
 * Permission Mapping for CMS RBAC System
 * Defines permissions and their role assignments
 */

declare(strict_types=1);

require_once __DIR__ . '/roles.php';

class Permissions {
    // Permission constants
    const MANAGE_USERS = 'manage_users';
    const MANAGE_CONTENT = 'manage_content';
    const PUBLISH_CONTENT = 'publish_content';
    const EDIT_CONTENT = 'edit_content';
    const VIEW_CONTENT = 'view_content';
    const MANAGE_SETTINGS = 'manage_settings';

    /**
     * Get all available permissions
     * @return array<string> List of permission constants
     */
    public static function all(): array {
        return [
            self::MANAGE_USERS,
            self::MANAGE_CONTENT,
            self::PUBLISH_CONTENT,
            self::EDIT_CONTENT,
            self::VIEW_CONTENT,
            self::MANAGE_SETTINGS
        ];
    }

    /**
     * Get role-permission mappings
     * @return array<string, array<string>> Role => Permissions map
     */
    public static function getRolePermissions(): array {
        return [
            Roles::ADMIN => self::all(),
            Roles::EDITOR => [
                self::MANAGE_CONTENT,
                self::PUBLISH_CONTENT,
                self::EDIT_CONTENT,
                self::VIEW_CONTENT
            ],
            Roles::VIEWER => [
                self::VIEW_CONTENT
            ]
        ];
    }

    /**
     * Check if permission exists
     * @param string $permission Permission to check
     * @return bool True if valid permission
     */
    public static function exists(string $permission): bool {
        return in_array($permission, self::all(), true);
    }
}
