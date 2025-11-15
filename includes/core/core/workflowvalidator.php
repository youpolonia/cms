<?php

class WorkflowValidator {
    private static $rules = [];
    private $errors = [];

    public static function addRule(string $currentState, string $nextState, callable $condition): void {
        if (!isset(self::$rules[$currentState])) {
            self::$rules[$currentState] = [];
        }
        self::$rules[$currentState][$nextState] = $condition;
    }

    public function validateTransition(string $currentState, string $nextState, array $context = []): bool {
        $this->errors = [];

        if (!isset(self::$rules[$currentState])) {
            $this->errors[] = "No transitions defined from $currentState";
            return false;
        }

        if (!isset(self::$rules[$currentState][$nextState])) {
            $this->errors[] = "Transition from $currentState to $nextState is not allowed";
            return false;
        }

        $condition = self::$rules[$currentState][$nextState];
        if (!$condition($context)) {
            $this->errors[] = "Transition conditions not met for $currentState → $nextState";
            return false;
        }

        return true;
    }

    public function getErrors(): array {
        return $this->errors;
    }

    public static function reset(): void {
        self::$rules = [];
    }
}
