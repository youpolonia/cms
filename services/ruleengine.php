<?php
declare(strict_types=1);

class RuleEngine {
    private static ?RuleEngine $instance = null;
    private array $rules = [];
    private array $triggers = [];
    private array $approvalChains = [];

    private function __construct() {
        // Initialize with default rules
        $this->loadDefaultRules();
    }

    public static function getInstance(): RuleEngine {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function loadDefaultRules(): void {
        // TODO: Load default rules from configuration
    }

    public function addRule(string $name, callable $condition, callable $action): void {
        $this->rules[$name] = [
            'condition' => $condition,
            'action' => $action
        ];
    }

    public function addTrigger(string $event, callable $handler): void {
        $this->triggers[$event][] = $handler;
    }

    public function addApprovalChain(string $workflow, array $steps): void {
        $this->approvalChains[$workflow] = $steps;
    }

    public function evaluate(string $event, array $context): void {
        if (isset($this->triggers[$event])) {
            foreach ($this->triggers[$event] as $handler) {
                $handler($context);
            }
        }

        foreach ($this->rules as $rule) {
            if ($rule['condition']($context)) {
                $rule['action']($context);
            }
        }
    }
}
