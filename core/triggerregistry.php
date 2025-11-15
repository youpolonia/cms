<?php
/**
 * Trigger Registry System
 */
class TriggerRegistry {
    private static array $triggers = [];
    private static array $eventMap = [];

    public static function register(string $type, string $class): void {
        if (!class_exists($class)) {
            throw new Exception("Trigger class {$class} does not exist");
        }
        self::$triggers[$type] = $class;
    }

    public static function mapEvent(string $eventName, string $triggerType): void {
        self::$eventMap[$eventName] = $triggerType;
    }

    public static function createTrigger(array $config): WorkflowTrigger {
        $type = $config['type'] ?? '';
        if (!isset(self::$triggers[$type])) {
            throw new Exception("Unknown trigger type: {$type}");
        }

        $class = self::$triggers[$type];
        return new $class($config);
    }

    public static function getTriggerForEvent(string $eventName): ?string {
        return self::$eventMap[$eventName] ?? null;
    }

    public static function getRegisteredTypes(): array {
        return array_keys(self::$triggers);
    }
}

// Register core trigger types
TriggerRegistry::register('system_event', SystemEventTrigger::class);
TriggerRegistry::register('scheduled', ScheduledTrigger::class);
TriggerRegistry::register('webhook', WebhookTrigger::class);

// Map system events
TriggerRegistry::mapEvent('login', 'system_event');
TriggerRegistry::mapEvent('logout', 'system_event');
TriggerRegistry::mapEvent('content_published', 'system_event');
TriggerRegistry::mapEvent('plugin_installed', 'system_event');
