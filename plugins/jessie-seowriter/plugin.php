<?php
namespace Plugins\JessieSeoWriter;

if (!defined('CMS_ROOT')) { define('CMS_ROOT', dirname(__DIR__, 2)); }
require_once CMS_ROOT . '/core/pluginsdk.php';

class JessieSeoWriter implements \Core\EnhancedPluginInterface {
    private static ?self $instance = null;

    public static function getInstance(): self {
        if (!self::$instance) self::$instance = new self();
        return self::$instance;
    }

    public function id(): string { return 'jessie-seowriter'; }
    public function name(): string { return 'Jessie SEO Writer'; }
    public function version(): string { return '1.0.0'; }
    public function activate(): void { require_once __DIR__ . '/install.php'; seowriter_install(); }
    public function deactivate(): void {}

    public function boot(): void {
        if (class_exists('\\Core\\EventBus')) {
            \Core\EventBus::getInstance()->dispatch('plugin.seowriter.ready');
        }
    }

    public function registerRoutes(): array {
        return [];
    }

    public function registerHooks(): array {
        return [];
    }
}

return JessieSeoWriter::getInstance();
