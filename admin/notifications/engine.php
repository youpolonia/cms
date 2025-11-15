<?php
/**
 * Notification Rules Engine
 * 
 * Handles rule evaluation, trigger matching, and notification processing
 * with tenant isolation and logging capabilities.
 */

namespace Admin\Notifications;

use Admin\Tenant\TenantManager;
use Admin\Logging\NotificationLogger;

class NotificationEngine
{
    private TenantManager $tenantManager;
    private NotificationLogger $logger;
    private array $activeRules = [];
    private array $triggerRegistry = [];

    public function __construct(TenantManager $tenantManager, NotificationLogger $logger)
    {
        $this->tenantManager = $tenantManager;
        $this->logger = $logger;
    }

    /**
     * Evaluate rules against a trigger event
     */
    public function evaluateRules(array $event): array
    {
        $this->validateTenantContext();
        
        try {
            $matchedRules = $this->matchTriggers($event);
            $results = [];
            
            foreach ($matchedRules as $rule) {
                if ($this->evaluateConditions($rule, $event)) {
                    $results[] = $this->processRule($rule, $event);
                }
            }
            
            return $results;
        } catch (\Throwable $e) {
            $this->logger->logError($e->getMessage(), [
                'event' => $event,
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException('Rule evaluation failed', 0, $e);
        }
    }

    /**
     * Register a trigger type with its matching callback
     */
    public function registerTrigger(string $triggerType, callable $matcher): void
    {
        $this->triggerRegistry[$triggerType] = $matcher;
    }

    /**
     * Load rules for current tenant
     */
    public function loadRules(array $rules): void
    {
        $this->activeRules = array_filter($rules, function($rule) {
            return $this->tenantManager->isRuleAllowedForTenant(
                $rule['id'], 
                $this->tenantManager->getCurrentTenantId()
            );
        });
    }

    private function validateTenantContext(): void
    {
        if (!$this->tenantManager->hasActiveTenant()) {
            throw new \RuntimeException('No active tenant context');
        }
    }

    private function matchTriggers(array $event): array
    {
        $matched = [];
        
        foreach ($this->activeRules as $rule) {
            if (isset($this->triggerRegistry[$rule['trigger_type']])) {
                $matcher = $this->triggerRegistry[$rule['trigger_type']];
                if ($matcher($event, $rule['trigger_params'])) {
                    $matched[] = $rule;
                }
            }
        }
        
        return $matched;
    }

    private function evaluateConditions(array $rule, array $event): bool
    {
        foreach ($rule['conditions'] as $condition) {
            if (!$this->evaluateCondition($condition, $event)) {
                return false;
            }
        }
        return true;
    }

    private function evaluateCondition(array $condition, array $event): bool
    {
        // Implement condition evaluation logic
        // Example: field comparisons, value checks, etc.
        return true; // Placeholder - implement actual logic
    }

    private function processRule(array $rule, array $event): array
    {
        $result = [
            'rule_id' => $rule['id'],
            'actions' => [],
            'processed_at' => time()
        ];

        foreach ($rule['actions'] as $action) {
            $result['actions'][] = $this->executeAction($action, $event);
        }

        $this->logger->logProcessing($rule['id'], $result);
        return $result;
    }

    private function executeAction(array $action, array $event): array
    {
        // Implement action execution
        return [
            'action_type' => $action['type'],
            'status' => 'pending', // Would be updated by actual execution
            'event_data' => $event
        ];
    }
}
