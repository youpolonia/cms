<?php
/**
 * Workflow Transition Handler - Pure PHP Implementation
 * Manages synchronous and asynchronous workflow state transitions
 */
class TransitionHandler {
    const LOCK_TIMEOUT = 30; // Seconds
    const ASYNC_QUEUE_TABLE = 'system_async_queue';
    
    /**
     * Execute synchronous transition with locking
     */
    public static function executeSync(int $workflowId, array $payload): array {
        $lockKey = "workflow_{$workflowId}";
        $lockAcquired = self::acquireLock($lockKey);
        
        if (!$lockAcquired) {
            throw new RuntimeException("Failed to acquire lock for workflow $workflowId");
        }

        try {
            $db = self::getDBConnection();
            $db->beginTransaction();

            // 1. Validate transition
            self::validateTransition($workflowId, $payload);
            
            // 2. Execute state change
            self::updateWorkflowState($workflowId, $payload['new_state']);
            
            // 3. Record activity
            self::logTransitionActivity($workflowId, $payload);
            
            $db->commit();
            
            return [
                'success' => true,
                'message' => 'Transition completed synchronously'
            ];
        } catch (Exception $e) {
            $db->rollBack();
            self::releaseLock($lockKey);
            self::logError($e);
            throw $e;
        } finally {
            self::releaseLock($lockKey);
        }
    }

    /**
     * Queue asynchronous transition
     */
    public static function executeAsync(int $workflowId, array $payload): void {
        try {
            $db = self::getDBConnection();
            $stmt = $db->prepare(
                "INSERT INTO ".self::ASYNC_QUEUE_TABLE." 
                (workflow_id, payload, created_at) 
                VALUES (?, ?, NOW())"
            );
            
            $serializedPayload = json_encode($payload);
            $stmt->execute([$workflowId, $serializedPayload]);
            
            self::logAsyncQueueStatus($workflowId);
        } catch (Exception $e) {
            self::logError($e);
            throw $e;
        }
    }

    private static function acquireLock(string $key): bool {
        // Implement distributed locking mechanism
        require_once __DIR__ . '/../../../core/tmp_sandbox.php';
        $lockFile = cms_tmp_path("{$key}.lock");
        $lockHandle = fopen($lockFile, 'w+');
        
        if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
            fclose($lockHandle);
            return false;
        }
        
        register_shutdown_function(function() use ($lockHandle, $lockFile) {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
            @unlink($lockFile);
        });
        
        return true;
    }

    private static function getDBConnection(): PDO {
        static $db = null;
        if (!$db) {
            require_once __DIR__ . '/../../../core/database.php';
            $db = \core\Database::connection();
        }
        return $db;
    }

    private static function logTransitionActivity(int $workflowId, array $payload): void {
        $db = self::getDBConnection();
        $stmt = $db->prepare(
            "INSERT INTO worker_activity_logs 
            (workflow_id, activity_type, details, created_at)
            VALUES (?, 'transition', ?, NOW())"
        );
        $stmt->execute([$workflowId, json_encode($payload)]);
    }

    private static function logError(Exception $e): void {
        error_log("[TransitionHandler] Error: ".$e->getMessage());
        if (class_exists('OperationalTransform')) {
            OperationalTransform::recordSystemEvent('transition_error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
