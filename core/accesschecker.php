<?php
class AccessChecker {
    /**
     * Check if current user has specified permission
     * @param string $permission Permission name to check
     * @return bool True if user has permission
     */
    public static function hasPermission(string $permission): bool {
        if (!isset($_SESSION['user_permissions'])) {
            return false;
        }
        return in_array($permission, $_SESSION['user_permissions']);
    }

    /**
     * Verify admin access
     * @return bool True if user is admin
     */
    public static function isAdmin(): bool {
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
}
