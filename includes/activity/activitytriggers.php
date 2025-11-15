<?php
require_once __DIR__ . '/../../core/database.php';

/**
 * Client Activity Triggers
 * 
 * Pure PHP implementation for tracking client-related activities
 */

class ActivityTriggers
{
    /**
     * Log client creation activity
     * 
     * @param int $userId The ID of the user performing the action
     * @param array $clientData The client data being created
     * @return bool True if logged successfully
     */
    public static function logClientCreation(int $userId, array $clientData): bool
    {
        return self::logActivity($userId, 'client_created', $clientData);
    }

    /**
     * Log client update activity
     * 
     * @param int $userId The ID of the user performing the action
     * @param array $oldData The client data before changes
     * @param array $newData The client data after changes
     * @return bool True if logged successfully
     */
    public static function logClientUpdate(int $userId, array $oldData, array $newData): bool
    {
        $changes = self::getDataChanges($oldData, $newData);
        return self::logActivity($userId, 'client_updated', $changes);
    }

    /**
     * Log client deletion activity
     * 
     * @param int $userId The ID of the user performing the action
     * @param array $clientData The client data being deleted
     * @return bool True if logged successfully
     */
    public static function logClientDeletion(int $userId, array $clientData): bool
    {
        return self::logActivity($userId, 'client_deleted', $clientData);
    }

    /**
     * Core logging method
     * 
     * @param int $userId
     * @param string $actionType
     * @param array $data
     * @return bool
     */
    protected static function logActivity(int $userId, string $actionType, array $data): bool
    {
        // Get database connection
        $pdo = \core\Database::connection();
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO activity_logs 
                (user_id, action_type, data, created_at) 
                VALUES (?, ?, ?, NOW())
            ");
            
            $jsonData = json_encode($data);
            
            return $stmt->execute([
                $userId,
                $actionType,
                $jsonData
            ]);
        } catch (PDOException $e) {
            error_log("Activity log failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Identify changes between old and new data
     * 
     * @param array $oldData
     * @param array $newData
     * @return array
     */
    protected static function getDataChanges(array $oldData, array $newData): array
    {
        $changes = [];
        
        foreach ($newData as $key => $value) {
            if (!array_key_exists($key, $oldData)) {
                $changes[$key] = [
                    'old' => null,
                    'new' => $value
                ];
            } elseif ($oldData[$key] !== $value) {
                $changes[$key] = [
                    'old' => $oldData[$key],
                    'new' => $value
                ];
            }
        }
        
        // Check for removed fields
        foreach ($oldData as $key => $value) {
            if (!array_key_exists($key, $newData)) {
                $changes[$key] = [
                    'old' => $value,
                    'new' => null
                ];
            }
        }
        
        return $changes;
    }
}
