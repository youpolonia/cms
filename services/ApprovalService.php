<?php
declare(strict_types=1);

class ApprovalService {
    private static ?ApprovalService $instance = null;
    private array $workflows = [];

    private function __construct() {
        // Initialize with default workflows
        $this->registerDefaultWorkflows();
    }

    public static function getInstance(): ApprovalService {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function registerDefaultWorkflows(): void {
        // Content publishing workflow
        $this->addWorkflow('content.publish', [
            'draft' => ['submit'],
            'pending_review' => ['approve', 'reject'],
            'approved' => ['publish', 'request_changes'],
            'published' => ['archive']
        ]);

        // User role assignment workflow
        $this->addWorkflow('user.role_change', [
            'requested' => ['approve', 'deny'],
            'approved' => ['implement'],
            'implemented' => ['verify']
        ]);
    }

    public function addWorkflow(string $name, array $steps): void {
        $this->workflows[$name] = $steps;
    }

    public function getNextSteps(string $workflow, string $currentState): array {
        return $this->workflows[$workflow][$currentState] ?? [];
    }

    public function processApproval(string $workflow, string $action, array $context): bool {
        $ruleEngine = RuleEngine::getInstance();
        $triggerService = TriggerService::getInstance();

        // Evaluate rules for this approval action
        $ruleEngine->evaluate("approval.$workflow.$action", $context);

        // Trigger appropriate events
        $triggerService->dispatch("approval.$workflow.$action", $context);

        return true;
    }
}
