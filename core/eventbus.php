<?php
namespace Core;

require_once __DIR__ . '/loggerfactory.php';

class EventBus {
    private static $instance;
    private $listeners = [];
    private $debugLog = [];
    private $lastEventId = 0;
    private $logger;

    private function __construct() {
        $this->logger = LoggerFactory::create();
    }

    public static function getInstance(): self {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function dispatch(string $event, array $payload = []): void {
        if (!isset($this->listeners[$event])) {
            return;
        }

        // Sort listeners by priority
        usort($this->listeners[$event], function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });

        foreach ($this->listeners[$event] as $listener) {
            try {
                call_user_func($listener['handler'], $payload);
                $this->logDebug($event, $listener['handler'], true, $payload);
            } catch (\Throwable $e) {
                $this->logDebug($event, $listener['handler'], false, $payload, $e->getMessage());
                $this->logger->error("EventBus error: {$e->getMessage()}", [
                    'event' => $event,
                    'handler' => is_array($listener['handler']) ?
                        implode('::', $listener['handler']) :
                        (is_string($listener['handler']) ? $listener['handler'] : 'closure'),
                    'payload' => $payload
                ]);
            }
        }
    }

    public function listen(string $event, callable $handler, int $priority = 50): void {
        $this->listeners[$event][] = [
            'handler' => $handler,
            'priority' => $priority
        ];
    }

    public function getDebugLog(): array {
        return $this->debugLog;
    }

    public function getRegisteredListeners(): array {
        return $this->listeners;
    }

    public function getNewEventsSince(int $lastId): array {
        return array_filter($this->debugLog, function($event) use ($lastId) {
            return $event['id'] > $lastId;
        });
    }

    private function logDebug(string $event, callable $handler, bool $success, array $payload, string $error = ''): void {
        $this->lastEventId++;
        $this->debugLog[] = [
            'id' => $this->lastEventId,
            'timestamp' => microtime(true),
            'event' => $event,
            'handler' => is_array($handler) ?
                implode('::', $handler) :
                (is_string($handler) ? $handler : 'closure'),
            'success' => $success,
            'payload' => $payload,
            'error' => $error
        ];
    }
}
