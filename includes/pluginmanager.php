<?php

namespace Includes;

use Includes\PluginLoader;
use Includes\AuditLogger;
use Includes\TenantManager;
class PluginManager {
    protected $plugins = [];
    protected $enabledPlugins = [];
    protected $hooks = [];
    protected $filters = [];
    protected $config;

    public function __construct() {
        if (file_exists(__DIR__ . '/../config/plugins.php')) {
            $this->config = require_once __DIR__ . '/../config/plugins.php';
        } else {
            $this->config = [];
        }
    }

    public function initialize() {
        $pluginDir = __DIR__ . '/../plugins';
        if (!is_dir($pluginDir)) {
            mkdir($pluginDir, 0755, true);
            return;
        }

        // Initialize PluginLoader
        $pluginLoader = new PluginLoader(
            $this,
            new AuditLogger(),
            class_exists('Includes\TenantManager') ? new TenantManager() : null
        );
        $pluginLoader->loadPlugins();

        // Legacy plugin support
        foreach (glob($pluginDir . '/*/plugin.php') as $pluginFile) {
            $plugin = require_once $pluginFile;
            if ($plugin instanceof PluginInterface) {
                $this->plugins[$plugin->getName()] = $plugin;
                $plugin->initialize($this);
            }
        }
    }

    public function getPlugins() {
        return $this->plugins;
    }

    public function enablePlugin(string $pluginName): bool {
        if (isset($this->plugins[$pluginName])) {
            $this->enabledPlugins[$pluginName] = true;
            return true;
        }
        return false;
    }

    public function disablePlugin(string $pluginName): bool {
        if (isset($this->plugins[$pluginName])) {
            unset($this->enabledPlugins[$pluginName]);
            return true;
        }
        return false;
    }

    public function isPluginEnabled(string $pluginName): bool {
        return isset($this->enabledPlugins[$pluginName]);
    }
    public function addHook($hookName, $callback) {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
        }
        $this->hooks[$hookName][] = $callback;
    }

    public function addFilter($filterName, $callback) {
        if (!isset($this->filters[$filterName])) {
            $this->filters[$filterName] = [];
        }
        $this->filters[$filterName][] = $callback;
    }
}

interface PluginInterface {
    public function getName();
    public function initialize($pluginManager = null);
}
