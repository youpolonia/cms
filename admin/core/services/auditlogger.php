<?php
/**
 * AuditLogger - Logs create/update/delete operations in the CMS
 * 
 * @package Core\Services
 */
class AuditLogger
{
    /**
     * Log a create operation
     * 
     * @param string $entityType Type of entity being created (e.g. 'content', 'user')
     * @param int $entityId ID of the created entity
     * @param array $data Entity data
     * @param int $userId ID of user performing the action
     * @return bool True if logged successfully
     */
    public static function logCreate(string $entityType, int $entityId, array $data, int $userId): bool
    {
        try {
            $logEntry = [
                'action' => 'create',
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'data' => json_encode($data),
                'user_id' => $userId,
                'timestamp' => time()
            ];
            
            return self::writeLog($logEntry);
        } catch (\Exception $e) {
            error_log("AuditLogger error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log an update operation
     * 
     * @param string $entityType Type of entity being updated
     * @param int $entityId ID of the updated entity
     * @param array $oldData Original entity data
     * @param array $newData Updated entity data
     * @param int $userId ID of user performing the action
     * @return bool True if logged successfully
     */
    public static function logUpdate(string $entityType, int $entityId, array $oldData, array $newData, int $userId): bool
    {
        try {
            $logEntry = [
                'action' => 'update',
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'old_data' => json_encode($oldData),
                'new_data' => json_encode($newData),
                'user_id' => $userId,
                'timestamp' => time()
            ];
            
            return self::writeLog($logEntry);
        } catch (\Exception $e) {
            error_log("AuditLogger error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Log a delete operation
     * 
     * @param string $entityType Type of entity being deleted
     * @param int $entityId ID of the deleted entity
     * @param array $data Entity data before deletion
     * @param int $userId ID of user performing the action
     * @return bool True if logged successfully
     */
    public static function logDelete(string $entityType, int $entityId, array $data, int $userId): bool
    {
        try {
            $logEntry = [
                'action' => 'delete',
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'data' => json_encode($data),
                'user_id' => $userId,
                'timestamp' => time()
            ];
            
            return self::writeLog($logEntry);
        } catch (\Exception $e) {
            error_log("AuditLogger error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Write log entry to storage
     * 
     * @param array $logEntry Associative array of log data
     * @return bool True if written successfully
     */
    private static function writeLog(array $logEntry): bool
    {
        // Ensure logs directory exists
        $logDir = __DIR__ . '/../../logs/audit/';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0755, true);
        }

        // Create log filename with date
        $logFile = $logDir . 'audit_' . date('Y-m-d') . '.log';

        // Format log entry
        $logLine = json_encode($logEntry) . PHP_EOL;

        // Append to log file
        return file_put_contents($logFile, $logLine, FILE_APPEND) !== false;
    }
}
