<?php

namespace Includes\Services;

class NotificationService
{
    private int $tenant_id;
    private array $eventRuleMap = [];
    private static array $subscribers = [];

    public function __construct(int $tenant_id)
    {
        $this->tenant_id = $tenant_id;
    }

    public static function subscribe(string $event, callable $callback): void
    {
        if (!isset(self::$subscribers[$event])) {
            self::$subscribers[$event] = [];
        }
        self::$subscribers[$event][] = $callback;
    }

    public function getTenantId(): int
    {
        return $this->tenant_id;
    }

    public function mapEventToRule(string $eventName, string $ruleName): void
    {
        $this->eventRuleMap[$eventName] = $ruleName;
    }

    public function getRuleForEvent(string $eventName): ?string
    {
        return $this->eventRuleMap[$eventName] ?? null;
    }

    public function handleEvent(string $eventName, array $payload): void
    {
        $payload['tenant_id'] = $this->tenant_id;

        // Process rule mapping if exists
        if ($rule = $this->getRuleForEvent($eventName)) {
            $this->processRule($rule, $payload);
        }

        // Process direct subscribers
        if (isset(self::$subscribers[$eventName])) {
            foreach (self::$subscribers[$eventName] as $callback) {
                call_user_func($callback, $payload);
            }
        }
    }

    private function processRule(string $ruleName, array $payload): void
    {
        // Implementation will be added when rule processing is defined
        // Payload already includes tenant_id
    }
}
