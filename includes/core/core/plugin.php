<?php

namespace Includes\Core;

/**
 * Plugin Engine for CMS
 *
 * Provides plugin discovery, loading, and hook system
 * Uses standardized bootstrap.php approach
 */
class Plugin
{
    /**
     * @var array Loaded plugins
     */
    protected static $loadedPlugins = [];

    /**
     * @var array Registered hooks
     */
    protected static $hooks = [];

    /**
     * @var string Minimum required CMS version
     */
    protected static $minCmsVersion = '1.0.0';

    /**
     * Discover and load all plugins
     */
    public static function loadAll(): void
    {
        $pluginsDir = __DIR__ . '/../../../plugins';
        if (!is_dir($pluginsDir)) {
            return;
        }

        $pluginDirs = array_filter(scandir($pluginsDir), function($item) use ($pluginsDir) {
            return $item !== '.' && $item !== '..' && is_dir($pluginsDir . '/' . $item);
        });

        foreach ($pluginDirs as $pluginDir) {
            $pluginPath = $pluginsDir . '/' . $pluginDir;
            self::load($pluginPath);
        }
    }

    /**
     * Load a single plugin
     */
    public static function load(string $pluginPath): void
    {
        // Security checks
        if (!is_dir($pluginPath) || !is_readable($pluginPath)) {
            throw new \RuntimeException("Plugin directory not accessible: " . $pluginPath);
        }

        $bootstrapFile = $pluginPath . '/bootstrap.php';
        $pluginJson = $pluginPath . '/plugin.json';

        // Check for required plugin.json
        if (!file_exists($pluginJson)) {
            throw new \RuntimeException("Missing plugin.json in: " . $pluginPath);
        }

        $pluginConfig = json_decode(file_get_contents($pluginJson), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("Invalid plugin.json in: " . $pluginPath);
        }

        // Version compatibility check
        if (isset($pluginConfig['requires']) && version_compare($pluginConfig['requires'], self::$minCmsVersion, '<')) {
            throw new \RuntimeException(sprintf(
                "Plugin requires CMS version %s or higher (current: %s)",
                $pluginConfig['requires'],
                self::$minCmsVersion
            ));
        }

        // Load bootstrap file if exists
        if (file_exists($bootstrapFile)) {
            require_once $bootstrapFile;
            self::$loadedPlugins[$pluginConfig['name']] = $pluginConfig;
        }
    }

    /**
     * Add action hook
     */
    public static function addAction(string $hook, callable $callback, int $priority = 10): void
    {
        self::addHook('action', $hook, $callback, $priority);
    }

    /**
     * Add filter hook
     */
    public static function addFilter(string $hook, callable $callback, int $priority = 10): void
    {
        self::addHook('filter', $hook, $callback, $priority);
    }

    /**
     * Execute action hooks
     */
    public static function doAction(string $hook, ...$args): void
    {
        self::executeHooks('action', $hook, $args);
    }

    /**
     * Apply filter hooks
     */
    public static function applyFilters(string $hook, $value, ...$args)
    {
        return self::executeHooks('filter', $hook, array_merge([$value], $args));
    }

    /**
     * Internal hook registration
     */
    protected static function addHook(string $type, string $hook, callable $callback, int $priority): void
    {
        if (!isset(self::$hooks[$type][$hook])) {
            self::$hooks[$type][$hook] = [];
        }

        self::$hooks[$type][$hook][$priority][] = $callback;
    }

    /**
     * Internal hook execution
     */
    protected static function executeHooks(string $type, string $hook, array $args)
    {
        if (!isset(self::$hooks[$type][$hook])) {
            return $type === 'filter' ? $args[0] : null;
        }

        ksort(self::$hooks[$type][$hook]);

        foreach (self::$hooks[$type][$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $result = call_user_func_array($callback, $args);
                
                if ($type === 'filter') {
                    $args[0] = $result;
                }
            }
        }

        return $type === 'filter' ? $args[0] : null;
    }

    /**
     * Get list of loaded plugins
     */
    public static function getLoadedPlugins(): array
    {
        return self::$loadedPlugins;
    }

    /**
     * Check if a plugin is loaded
     */
    public static function isPluginLoaded(string $pluginName): bool
    {
        return isset(self::$loadedPlugins[$pluginName]);
    }
}
