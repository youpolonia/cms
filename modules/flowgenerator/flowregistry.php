<?php
class FlowRegistry {
    private static $workflows = [];

    public static function registerWorkflow(string $id, array $definition): void {
        self::$workflows[$id] = $definition;
    }

    public static function getWorkflow(string $id): ?array {
        return self::$workflows[$id] ?? null;
    }

    public static function getAllWorkflows(): array {
        return self::$workflows;
    }

    public static function validateWorkflow(array $workflow): bool {
        // Basic validation
        return isset($workflow['workflow_id'], 
                    $workflow['trigger_event'], 
                    $workflow['n8n_webhook']);
    }
}
