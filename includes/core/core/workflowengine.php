<?php

namespace CMS\Core;

class WorkflowEngine
{
    private $workflows = [];
    private $currentState;
    private $tenantId;

    public function __construct(string $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->currentState = 'draft';
    }

    public function defineWorkflow(string $name, array $states, array $transitions): void
    {
        $this->workflows[$name] = [
            'states' => $states,
            'transitions' => $transitions
        ];
    }

    public function getCurrentState(): string
    {
        return $this->currentState;
    }

    public function transition(string $workflowName, string $transitionName): bool
    {
        if (!isset($this->workflows[$workflowName])) {
            return false;
        }

        $transition = $this->workflows[$workflowName]['transitions'][$transitionName] ?? null;
        if (!$transition || $transition['from'] !== $this->currentState) {
            return false;
        }

        $this->currentState = $transition['to'];
        return true;
    }

    public function getAvailableTransitions(string $workflowName): array
    {
        if (!isset($this->workflows[$workflowName])) {
            return [];
        }

        return array_filter(
            $this->workflows[$workflowName]['transitions'],
            fn($t) => $t['from'] === $this->currentState
        );
    }

    public function executeAction(string $workflowName, string $action, array $payload = []): bool
    {
        if (!isset($this->workflows[$workflowName]['actions'][$action])) {
            return false;
        }

        $actionConfig = $this->workflows[$workflowName]['actions'][$action];
        if (!in_array($this->currentState, $actionConfig['allowed_states'])) {
            return false;
        }

        // Execute action callback if provided
        if (isset($actionConfig['callback']) && is_callable($actionConfig['callback'])) {
            return $actionConfig['callback']($payload);
        }

        return true;
    }
}
