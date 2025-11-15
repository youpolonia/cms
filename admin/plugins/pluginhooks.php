<?php
require_once __DIR__ . '/pluginhealthmonitor.php';
/**
 * Plugin Hook System
 * Handles activation/deactivation hooks for plugins
 */
class PluginHooks {
    private static $hooks = [
        'activation' => [],
        'deactivation' => []
    ];

    /**
     * Register a hook callback
     * @param string $hookType 'activation' or 'deactivation'
     * @param string $pluginId Unique plugin identifier
     * @param callable $callback Callback function
     */
    public static function register(string $hookType, string $pluginId, callable $callback): void {
        if (!isset(self::$hooks[$hookType])) {
            throw new InvalidArgumentException("Invalid hook type: $hookType");
        }
        self::$hooks[$hookType][$pluginId] = $callback;
    }

    /**
     * Trigger hooks for a plugin
     * @param string $hookType 'activation' or 'deactivation'
     * @param string $pluginId Unique plugin identifier
     */
    public static function trigger(string $hookType, string $pluginId): bool {
        if (!isset(self::$hooks[$hookType][$pluginId])) {
            return false;
        }

        try {
            call_user_func(self::$hooks[$hookType][$pluginId]);
            
            // Record activation status in health monitor
            $monitor = PluginHealthMonitor::getInstance();
            $monitor->logActivation($pluginId, $hookType === 'activation');
            
            return true;
        } catch (Exception $e) {
            error_log("Plugin hook failed: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Get all registered hooks
     * @return array Hook registry
     */
    public static function getHooks(): array {
        return self::$hooks;
    }
}
