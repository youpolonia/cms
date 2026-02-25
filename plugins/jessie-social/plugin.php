<?php
namespace Plugins\JessieSocial;

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/core/pluginsdk.php';

class JessieSocial implements \Core\EnhancedPluginInterface {
    private static ?self $instance = null;
    public static function getInstance(): self { if (!self::$instance) self::$instance = new self(); return self::$instance; }
    public function id(): string { return 'jessie-social'; }
    public function name(): string { return 'Jessie Social Media Scheduler'; }
    public function version(): string { return '1.0.0'; }
    public function activate(): void { require_once __DIR__ . '/install.php'; social_install(); }
    public function deactivate(): void {}
    public function boot(): void {}
    public function registerRoutes(): array { return []; }
    public function registerHooks(): array { return []; }
}
return JessieSocial::getInstance();
