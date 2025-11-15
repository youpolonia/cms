<?php
class WorkflowEngine {
    private static $instance;
    private $pdo;
    private $actions = [];

    private function __construct(\PDO $pdo) {
        $this->pdo = $pdo;
    }

    public static function getInstance(\PDO $pdo): self {
        if (!isset(self::$instance)) {
            self::$instance = new self($pdo);
        }
        return self::$instance;
    }

    /**
     * Register workflow action
     * @param string $trigger - State transition trigger (e.g. "draft_to_pending")
     * @param callable $action - Action callback
     */
    public function registerAction(string $trigger, callable $action): void {
        $this->actions[$trigger][] = $action;
    }

    /**
     * Execute actions for a state transition
     * @param string $fromState - Current state
     * @param string $toState - Target state
     * @param array $context - Transition context
     */
    public function executeActions(
        string $fromState,
        string $toState,
        array $context
    ): void {
        $trigger = "{$fromState}_to_{$toState}";
        
        if (isset($this->actions[$trigger])) {
            foreach ($this->actions[$trigger] as $action) {
                call_user_func($action, $context);
            }
        }
    }

    /**
     * Get registered actions for testing
     */
    public function getRegisteredActions(): array {
        return $this->actions;
    }
}
