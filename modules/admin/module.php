<?php
/**
 * Admin Module Implementation
 */

class AdminModule {
    public static function init() {
        self::registerRoutes();
        self::registerAdminMenu();
    }

    public static function registerRoutes() {
        // Register admin routes directly with Router
        \core\Router::get('/admin/dashboard', [self::class, 'dashboard']);
        \core\Router::get('/admin/users', [self::class, 'listUsers']);
        \core\Router::get('/admin/settings', [self::class, 'systemSettings']);
    }

    public static function registerAdminMenu() {
        // Store menu items in static property
        self::$menuItems = [
            ['Dashboard', '/admin/dashboard', 'admin_dashboard'],
            ['Users', '/admin/users', 'admin_users'],
            ['Settings', '/admin/settings', 'admin_settings']
        ];
    }

    private static $menuItems = [];

    public static function dashboard() {
        $data = [
            'title' => 'Admin Dashboard',
            'stats' => [
                'users' => self::getUserCount(),
                'content' => self::getContentCount()
            ]
        ];
        require_once __DIR__ . '/views/dashboard.php';
        exit;
    }

    public static function listUsers() {
        $data = [
            'title' => 'User Management',
            'users' => self::getUsers()
        ];
        require_once __DIR__ . '/views/index.php';
        exit;
    }

    public static function systemSettings() {
        $data = [
            'title' => 'System Configuration',
            'settings' => self::getSystemSettings()
        ];
        require_once __DIR__ . '/views/settings.php';
        exit;
    }

    private static function getUserCount() {
        // TODO: Implement actual user count
        return 0;
    }

    private static function getContentCount() {
        // TODO: Implement actual content count
        return 0;
    }

    private static function getUsers() {
        // TODO: Implement actual user listing
        return [];
    }

    private static function getSystemSettings() {
        // TODO: Implement actual settings retrieval
        return [];
    }
}
