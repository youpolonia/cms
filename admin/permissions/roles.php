<?php
/**
 * Role Definitions for CMS RBAC System
 * Defines core roles: admin, editor, viewer
 */

declare(strict_types=1);

class Roles {
    const ADMIN = 'admin';
    const EDITOR = 'editor';
    const VIEWER = 'viewer';

    /**
     * Get all available roles
     * @return array<string> List of role constants
     */
    public static function all(): array {
        return [
            self::ADMIN,
            self::EDITOR, 
            self::VIEWER
        ];
    }

    /**
     * Check if role exists
     * @param string $role Role to check
     * @return bool True if valid role
     */
    public static function exists(string $role): bool {
        return in_array($role, self::all(), true);
    }
}
