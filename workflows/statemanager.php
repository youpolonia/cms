<?php
/**
 * Workflow State Management System
 * Handles state transitions and persistence for workflow execution
 */
class StateManager {
    private $stateFile;
    private $currentState;
    private $validStates;
    private $transitionRules = [];

    public function __construct(string $stateFile, array $validStates) {
        $this->stateFile = $stateFile;
        $this->validStates = $validStates;
        $this->loadState();
    }

    public function getCurrentState(): string {
        return $this->currentState;
    }

    public function setInitialState(string $state): void {
        if (!in_array($state, $this->validStates)) {
            throw new InvalidArgumentException("Invalid initial state: $state");
        }
        $this->currentState = $state;
        $this->saveState();
    }

    public function addTransitionRule(string $fromState, string $toState, callable $handler): void {
        if (!in_array($fromState, $this->validStates) || !in_array($toState, $this->validStates)) {
            throw new InvalidArgumentException("Invalid state in transition rule");
        }
        $this->transitionRules[$fromState][$toState] = $handler;
    }

    public function transitionTo(string $newState): bool {
        if (!in_array($newState, $this->validStates)) {
            throw new InvalidArgumentException("Invalid target state: $newState");
        }

        // Check if transition is allowed
        if (!isset($this->transitionRules[$this->currentState][$newState])) {
            return false;
        }

        // Execute transition handler
        $handler = $this->transitionRules[$this->currentState][$newState];
        if ($handler($this->currentState, $newState)) {
            $this->currentState = $newState;
            $this->saveState();
            return true;
        }

        return false;
    }

    private function loadState(): void {
        if (file_exists($this->stateFile)) {
            $stateData = @json_decode(file_get_contents($this->stateFile), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new RuntimeException("Failed to decode state file: " . json_last_error_msg());
            }
            $this->currentState = $stateData['current_state'] ?? reset($this->validStates);
        } else {
            $this->currentState = reset($this->validStates);
        }
    }

    private function saveState(): void {
        $stateData = [
            'current_state' => $this->currentState,
            'timestamp' => time()
        ];
        $result = @file_put_contents($this->stateFile, json_encode($stateData));
        if ($result === false) {
            throw new RuntimeException("Failed to save state to file");
        }
    }
}
