<?php

namespace Includes\Plugins;

require_once __DIR__ . '/../errorhandler.php';
require_once __DIR__ . '/../FileCache.php';
require_once __DIR__ . '/../database/tenantcontext.php';
require_once __DIR__ . '/dependencies/dependencyresolver.php';
require_once __DIR__ . '/ui/uiextensionmanager.php';
require_once __DIR__ . '/pluginsandbox.php';
use Includes\ErrorHandler;
use Includes\FileCache;
use Includes\Database\TenantContext;
use Includes\Plugins\Dependencies\DependencyResolver;
use Includes\Plugins\UI\UIExtensionManager;

class PluginManager {
    private static $instance;
    private $plugins = [];
    private $activePlugins = [];
    private $cache;
    private $sandbox;
    private $dependencyResolver;
    private $uiExtensionManager;
    private $tenantId;

    private function __construct() {
        $this->tenantId = TenantContext::getCurrentTenantId();
        $this->cache = new FileCache('plugins');
        $this->sandbox = new PluginSandbox('PluginManager', $this->tenantId);
        $this->dependencyResolver = new DependencyResolver($this->tenantId);
        $this->uiExtensionManager = new UIExtensionManager($this->tenantId);
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function loadAll(): void {
        $pluginsDir = CMS_ROOT . '/plugins';
        if (!is_dir($pluginsDir)) {
            ErrorHandler::log("Plugins directory not found: $pluginsDir");
            return;
        }

        $pluginDirs = array_filter(glob("$pluginsDir/*"), 'is_dir');
        foreach ($pluginDirs as $dir) {
            $this->loadPlugin(basename($dir));
        }
    }

    public function loadPlugin(string $pluginName): bool {
        $manifest = $this->validatePlugin($pluginName);
        if (!$manifest) {
            return false;
        }

        $this->plugins[$pluginName] = $manifest;
        $this->activatePlugin($pluginName);
        return true;
    }

    private function validatePlugin(string $pluginName): ?array {
        $pluginDir = CMS_ROOT . "/plugins/$pluginName";
        $manifestFile = "$pluginDir/plugin.json";

        if (!file_exists($manifestFile)) {
            ErrorHandler::log("Plugin manifest missing for: $pluginName");
            return null;
        }

        $manifest = json_decode(file_get_contents($manifestFile), true);
        if (!$manifest) {
            ErrorHandler::log("Invalid plugin manifest for: $pluginName");
            return null;
        }

        $required = ['name', 'version', 'description', 'author', 'path'];
        foreach ($required as $field) {
            if (!isset($manifest[$field])) {
                ErrorHandler::log("Plugin $pluginName missing required field: $field");
                return null;
            }
        }

        return $manifest;
    }

    public function activatePlugin(string $pluginName): bool {
        if (!isset($this->plugins[$pluginName])) {
            return false;
        }

        // Check dependencies
        if (!$this->dependencyResolver->resolve($this->plugins[$pluginName])) {
            return false;
        }

        // Load plugin in sandbox
        $pluginFile = $this->plugins[$pluginName]['path'] . '/plugin.php';
        if (file_exists($pluginFile)) {
            $this->sandbox->execute(function() use ($pluginFile) {
                $__base = realpath(CMS_ROOT . '/plugins');
                $__target = realpath($pluginFile);
                if ($__base === false || $__target === false || !str_starts_with($__target, $__base . DIRECTORY_SEPARATOR) || !is_file($__target)) {
                    http_response_code(400);
                    error_log('Blocked invalid include path: ' . ($pluginFile ?? 'unknown'));
                    return;
                }
                require_once $__target;
            });
        }

        $this->activePlugins[$pluginName] = $this->plugins[$pluginName];
        $this->uiExtensionManager->clearCache();
        return true;
    }

    public function getPlugin(string $pluginName): ?array {
        return $this->plugins[$pluginName] ?? null;
    }

    public function getAllPlugins(): array {
        return $this->plugins;
    }

    public function isActive(string $pluginName): bool {
        return isset($this->activePlugins[$pluginName]);
    }

    public function getSandbox(): PluginSandbox {
        return $this->sandbox;
    }

    public function getDependencyResolver(): DependencyResolver {
        return $this->dependencyResolver;
    }

    public function getUIExtensionManager(): UIExtensionManager {
        return $this->uiExtensionManager;
    }

    public function renderUIExtensions(string $point, array $context = []): string {
        return $this->uiExtensionManager->renderExtensions($point, $context);
    }
}
