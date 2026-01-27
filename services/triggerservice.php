<?php
declare(strict_types=1);

class TriggerService {
    private static ?TriggerService $instance = null;
    private array $listeners = [];

    private function __construct() {
        // Initialize with core events
        $this->registerCoreEvents();
    }

    public static function getInstance(): TriggerService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function registerCoreEvents(): void {
        // Core system events
        $this->addListener('content.published', function($data) {
            RuleEngine::getInstance()->evaluate('content.published', $data);
        });
        
        $this->addListener('user.registered', function($data) {
            RuleEngine::getInstance()->evaluate('user.registered', $data);
        });
    }

    public function addListener(string $event, callable $handler): void {
        $this->listeners[$event][] = $handler;
    }

    public function dispatch(string $event, array $data = []): void {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $handler) {
                $handler($data);
            }
        }
    }
}
