<?php
/**
 * Event Dispatcher System
 */
class EventDispatcher {
    private static $hooks = [];

    /**
     * Register a hook
     */
    public static function registerHook(string $name, callable $callback, int $priority = 10) {
        if (!isset(self::$hooks[$name])) {
            self::$hooks[$name] = [];
        }
        self::$hooks[$name][] = [
            'callback' => $callback,
            'priority' => $priority
        ];
        
        // Sort hooks by priority
        usort(self::$hooks[$name], function($a, $b) {
            return $a['priority'] <=> $b['priority'];
        });
    }

    /**
     * Dispatch an event
     */
    public static function dispatch(string $name, array $args = []) {
        if (!isset(self::$hooks[$name])) {
            return;
        }

        foreach (self::$hooks[$name] as $hook) {
            call_user_func_array($hook['callback'], $args);
        }
    }

    /**
     * Get all registered hooks
     */
    public static function getHooks(): array {
        return self::$hooks;
    }

    /**
     * Load hooks from database
     */
    public static function loadHooksFromDatabase() {
        $dbHooks = Hook::getAll();
        foreach ($dbHooks as $hook) {
            if (function_exists($hook->name)) {
                self::registerHook($hook->name, $hook->name);
            }
        }
    }
}

// Initialize by loading hooks from database
EventDispatcher::loadHooksFromDatabase();
