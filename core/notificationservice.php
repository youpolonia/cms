<?php
/**
 * NotificationService - Minimal stub for bootstrap compatibility
 * Provides static init() and subscribe() methods
 */
class NotificationService {
    private static array $config = [];
    private static array $subscribers = [];

    /**
     * Initialize notification service
     * @param array $config Configuration array
     */
    public static function init(array $config): void {
        self::$config = $config;
    }

    /**
     * Subscribe to notification events
     * @param string $event Event name
     * @param callable $callback Callback function
     */
    public static function subscribe(string $event, callable $callback): void {
        if (!isset(self::$subscribers[$event])) {
            self::$subscribers[$event] = [];
        }
        self::$subscribers[$event][] = $callback;
    }

    /**
     * Dispatch notification event
     * @param string $event Event name
     * @param array $data Event data
     */
    public static function dispatch(string $event, array $data): void {
        if (isset(self::$subscribers[$event])) {
            foreach (self::$subscribers[$event] as $callback) {
                try {
                    call_user_func($callback, $data);
                } catch (\Throwable $e) {
                    error_log("NotificationService dispatch error: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Get configuration
     * @return array
     */
    public static function getConfig(): array {
        return self::$config;
    }
}
