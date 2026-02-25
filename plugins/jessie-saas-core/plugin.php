<?php
namespace Plugins\JessieSaasCore;

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/core/pluginsdk.php';

class JessieSaasCore implements \Core\EnhancedPluginInterface {
    private static ?self $instance = null;
    
    public static function getInstance(): self {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }
    
    public function id(): string { return 'jessie-saas-core'; }
    public function name(): string { return 'Jessie SaaS Core'; }
    public function version(): string { return '1.0.0'; }
    public function activate(): void { require_once __DIR__ . '/install.php'; saas_core_install(); }
    public function deactivate(): void {}
    
    public function boot(): void {
        // Register hook for all SaaS API routes
        if (class_exists('\\Core\\EventBus')) {
            \Core\EventBus::getInstance()->dispatch('plugin.saas-core.ready');
        }
    }
    
    public function registerRoutes(): array {
        return [
            'GET /saas/login' => 'auth_login_page',
            'POST /saas/login' => 'auth_login',
            'GET /saas/register' => 'auth_register_page',
            'POST /saas/register' => 'auth_register',
            'GET /saas/logout' => 'auth_logout',
            'GET /saas/dashboard' => 'dashboard',
        ];
    }
    
    public function registerHooks(): array {
        return [];
    }
}

return JessieSaasCore::getInstance();
