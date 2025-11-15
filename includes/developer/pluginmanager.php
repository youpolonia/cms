<?php
declare(strict_types=1);

/**
 * Developer Platform - Plugin Manager
 * Handles plugin registration, loading, and management
 */
class PluginManager {
    private static array $plugins = [];
    private static array $hooks = [];
    private static string $pluginDir = __DIR__ . '/../../plugins/';

    /**
     * Register a plugin
     */
    public static function registerPlugin(
        string $pluginName,
        string $mainFile,
        array $metadata = []
    ): bool {
        if (isset(self::$plugins[$pluginName])) {
            throw new RuntimeException("Plugin already registered: $pluginName");
        }

        self::$plugins[$pluginName] = [
            'main_file' => $mainFile,
            'metadata' => $metadata,
            'active' => false,
            'version' => $metadata['version'] ?? '1.0.0'
        ];

        self::logEvent("Plugin registered: $pluginName");
        return true;
    }

    /**
     * Activate a plugin
     */
    public static function activatePlugin(string $pluginName): bool {
        if (!isset(self::$plugins[$pluginName])) {
            throw new InvalidArgumentException("Plugin not found: $pluginName");
        }

        $plugin = &self::$plugins[$pluginName];
        require_once self::$pluginDir . $plugin['main_file'];

        $plugin['active'] = true;
        self::logEvent("Plugin activated: $pluginName");
        return true;
    }

    /**
     * Add a hook/action
     */
    public static function addHook(
        string $hookName,
        callable $callback,
        int $priority = 10
    ): void {
        self::$hooks[$hookName][$priority][] = $callback;
        ksort(self::$hooks[$hookName]);
    }

    /**
     * Execute hooks/actions
     */
    public static function doHook(string $hookName, mixed ...$args): void {
        if (!isset(self::$hooks[$hookName])) {
            return;
        }

        foreach (self::$hooks[$hookName] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                call_user_func_array($callback, $args);
            }
        }
    }

    private static function logEvent(string $message): void {
        file_put_contents(
            __DIR__ . '/../logs/plugin_events.log',
            date('Y-m-d H:i:s') . " - $message\n",
            FILE_APPEND
        );
    }

    // BREAKPOINT: Continue with plugin management features
}
