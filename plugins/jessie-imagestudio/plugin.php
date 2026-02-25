<?php
namespace Plugins\JessieImagestudio;

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/core/pluginsdk.php';

class JessieImagestudio implements \Core\EnhancedPluginInterface {
    private static ?self $instance = null;

    public static function getInstance(): self {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function id(): string { return 'jessie-imagestudio'; }
    public function name(): string { return 'Jessie Image Studio'; }
    public function version(): string { return '1.0.0'; }

    public function activate(): void {
        require_once __DIR__ . '/install.php';
        imagestudio_install();
    }

    public function deactivate(): void {}

    public function boot(): void {}

    public function registerRoutes(): array {
        return [
            'GET /saas/images'    => 'frontend_router',
            'POST /api/imagestudio' => 'api_router',
        ];
    }

    public function registerHooks(): array {
        return [];
    }
}

return JessieImagestudio::getInstance();
