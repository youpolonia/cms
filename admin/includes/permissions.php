<?php
/**
 * Admin RBAC Helper Functions
 * Minimal permission checking for admin panel
 */

if (!function_exists('cms_get_current_admin')) {
    /**
     * Get current admin user from session
     * @return array|null Admin user data or null if not logged in
     */
    function cms_get_current_admin(): ?array {
        if (!isset($_SESSION['admin_id'])) {
            return null;
        }

        if (!isset($_SESSION['admin_role'])) {
            return null;
        }

        return [
            'id' => $_SESSION['admin_id'] ?? null,
            'role' => $_SESSION['admin_role'] ?? null,
            'username' => $_SESSION['admin_username'] ?? null,
        ];
    }
}

if (!function_exists('cms_require_admin_role')) {
    /**
     * Require admin role - redirect to login if not logged in
     * @return void
     */
    function cms_require_admin_role(): void {
        $admin = cms_get_current_admin();

        if ($admin === null) {
            // Store intended URL for redirect after login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/admin';
            header('Location: /admin/login');
            exit;
        }

        // Check if user has admin role
        if (!isset($admin['role']) || $admin['role'] !== 'admin') {
            http_response_code(403);
            exit('Access Denied: Administrator privileges required');
        }
    }
}

if (!function_exists('cms_require_permission')) {
    /**
     * Require specific permission - redirect to login if not logged in
     * @param string $permission Permission key to check
     * @return void
     */
    function cms_require_permission(string $permission): void {
        $admin = cms_get_current_admin();

        if ($admin === null) {
            // Store intended URL for redirect after login
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/admin';
            header('Location: /admin/login');
            exit;
        }

        // Admin role has all permissions
        if (isset($admin['role']) && $admin['role'] === 'admin') {
            return;
        }

        // Check permission in session or database
        // For now, require admin role for all permissions
        // TODO: Implement granular permission checking
        http_response_code(403);
        exit('Access Denied: Insufficient permissions');
    }
}
