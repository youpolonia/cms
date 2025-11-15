<?php
/**
 * Handles status transitions for content federation
 * Follows framework-free PHP requirements
 */
class StatusTransitionHandler {
    private static $transitions = [];
    private static $logger;
    private static $workflowEngine;

    /**
     * Initialize with workflow engine
     */
    public static function init(WorkflowEngine $workflowEngine): void {
        self::$workflowEngine = $workflowEngine;
        
        // Phase 5 transition rules
        self::registerTransition('draft', 'pending', function(array $context) {
            // Validate draft to pending transition
            if (empty($context['content_id'])) {
                throw new Exception('Content ID required for transition');
            }
            
            // Execute workflow actions
            self::$workflowEngine->executeActions('draft', 'pending', $context);
            
            return ['success' => true];
        });

        self::registerTransition('pending', 'published', function(array $context) {
            // Validate pending to published transition
            if (empty($context['approver_id'])) {
                throw new Exception('Approver ID required for publishing');
            }
            
            self::$workflowEngine->executeActions('pending', 'published', $context);
            return ['success' => true];
        });

        self::registerTransition('published', 'archived', function(array $context) {
            // Validate published to archived transition
            if (empty($context['archival_reason'])) {
                throw new Exception('Archival reason required');
            }
            
            self::$workflowEngine->executeActions('published', 'archived', $context);
            return ['success' => true];
        });
    }

    /**
     * Register a status transition
     */
    public static function registerTransition(
        string $fromState,
        string $toState,
        callable $handler
    ): void {
        if (!isset(self::$transitions[$fromState])) {
            self::$transitions[$fromState] = [];
        }
        self::$transitions[$fromState][$toState] = $handler;
    }

    /**
     * Execute a status transition
     */
    public static function executeTransition(
        string $fromState,
        string $toState,
        array $context = []
    ): array {
        if (!isset(self::$transitions[$fromState][$toState])) {
            throw new Exception("Invalid transition from $fromState to $toState");
        }

        try {
            $result = call_user_func(
                self::$transitions[$fromState][$toState],
                $context
            );
            self::logTransition($fromState, $toState, $context, true);
            
            // Notify subscribers of successful transition
            NotificationService::notify("content_state_changed", [
                'from_state' => $fromState,
                'to_state' => $toState,
                'content_id' => $context['content_id'] ?? null,
                'tenant_id' => $context['tenant_id'] ?? null
            ]);
            
            return $result;
        } catch (Exception $e) {
            self::logTransition($fromState, $toState, $context, false, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Log transition attempt
     */
    private static function logTransition(
        string $fromState,
        string $toState,
        array $context,
        bool $success,
        string $error = null
    ): void {
        $logEntry = [
            'timestamp' => time(),
            'from_state' => $fromState,
            'to_state' => $toState,
            'success' => $success,
            'context' => $context
        ];

        if ($error) {
            $logEntry['error'] = $error;
        }

        if (self::$logger) {
            call_user_func(self::$logger, $logEntry);
        }
    }

    /**
     * Set custom logger function
     */
    public static function setLogger(callable $logger): void {
        self::$logger = $logger;
    }
}
