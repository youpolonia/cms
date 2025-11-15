<?php
/**
 * RecurrenceEngine - Handles content scheduling with version tracking
 * 
 * @package CMS
 * @subpackage Services
 */

class RecurrenceEngine {
    /**
     * @var VersionedScheduleService $versionedScheduleService
     */
    private $versionedScheduleService;

    /**
     * @var PDO $db Database connection
     */
    private $db;

    /**
     * Constructor
     * 
     * @param PDO $db Database connection
     * @param VersionedScheduleService $versionedScheduleService
     */
    public function __construct(PDO $db, VersionedScheduleService $versionedScheduleService) {
        $this->db = $db;
        $this->versionedScheduleService = $versionedScheduleService;
    }

    /**
     * Create a new recurrence rule
     * 
     * @param array $params {
     *     @type int $content_id
     *     @type string $content_type
     *     @type string $pattern_type (daily|weekly|monthly|yearly)
     *     @type array $pattern_params Pattern-specific parameters
     *     @type string $start_date
     *     @type string|null $end_date
     *     @type int $user_id
     * }
     * @return array Result with success status and message
     */
    public function createRecurrence(array $params): array {
        // Validate RBAC permissions
        if (!$this->checkSchedulingPermissions($params['user_id'])) {
            return ['success' => false, 'message' => 'Insufficient permissions'];
        }

        // Validate content type restrictions
        if (!$this->validateContentType($params['content_type'])) {
            return ['success' => false, 'message' => 'Invalid content type for scheduling'];
        }

        // Check for conflicts with existing scheduled events
        if ($this->hasSchedulingConflicts($params)) {
            return ['success' => false, 'message' => 'Scheduling conflict detected'];
        }

        // Generate versioned recurrence pattern
        $pattern = $this->generateRecurrencePattern($params);

        try {
            $this->db->beginTransaction();

            // Store the recurrence rule
            $stmt = $this->db->prepare("
                INSERT INTO scheduled_events 
                (content_id, content_type, pattern_type, pattern_params, start_date, end_date, user_id, version_hash)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $params['content_id'],
                $params['content_type'],
                $params['pattern_type'],
                json_encode($pattern['params']),
                $params['start_date'],
                $params['end_date'],
                $params['user_id'],
                $pattern['version_hash']
            ]);

            // Record version history
            $this->versionedScheduleService->recordVersion(
                $params['content_id'],
                $pattern['version_hash'],
                $params['user_id']
            );

            $this->db->commit();
            return ['success' => true, 'message' => 'Recurrence rule created'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Generate versioned recurrence pattern
     * 
     * @param array $params Recurrence parameters
     * @return array Pattern data with version hash
     */
    private function generateRecurrencePattern(array $params): array {
        $basePattern = [
            'type' => $params['pattern_type'],
            'start' => $params['start_date'],
            'end' => $params['end_date'] ?? null,
            'content_id' => $params['content_id']
        ];

        // Add pattern-specific parameters
        switch ($params['pattern_type']) {
            case 'daily':
                $basePattern['interval'] = $params['pattern_params']['interval'] ?? 1;
                break;
            case 'weekly':
                $basePattern['days'] = $params['pattern_params']['days'] ?? [1]; // Default Monday
                $basePattern['interval'] = $params['pattern_params']['interval'] ?? 1;
                break;
            case 'monthly':
                $basePattern['day_of_month'] = $params['pattern_params']['day_of_month'] ?? 1;
                $basePattern['interval'] = $params['pattern_params']['interval'] ?? 1;
                break;
            case 'yearly':
                $basePattern['month'] = $params['pattern_params']['month'] ?? 1;
                $basePattern['day_of_month'] = $params['pattern_params']['day_of_month'] ?? 1;
                break;
        }

        // Create version hash
        $versionHash = hash('sha256', json_encode($basePattern));

        return [
            'params' => $basePattern,
            'version_hash' => $versionHash
        ];
    }

    /**
     * Check for scheduling conflicts
     * 
     * @param array $params Recurrence parameters
     * @return bool True if conflicts exist
     */
    private function hasSchedulingConflicts(array $params): bool {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM scheduled_events 
            WHERE content_id = ? 
            AND (
                (start_date BETWEEN ? AND ?) 
                OR (end_date BETWEEN ? AND ?)
                OR (? BETWEEN start_date AND end_date)
            )
        ");
        
        $endDate = $params['end_date'] ?? date('Y-m-d', strtotime('+1 year', strtotime($params['start_date'])));
        
        $stmt->execute([
            $params['content_id'],
            $params['start_date'],
            $endDate,
            $params['start_date'],
            $endDate,
            $params['start_date']
        ]);

        return $stmt->fetchColumn() > 0;
    }

    /**
     * Validate content type for scheduling
     * 
     * @param string $contentType
     * @return bool True if valid
     */
    private function validateContentType(string $contentType): bool {
        // In a real implementation, this would check against a config or DB table
        $allowedTypes = ['article', 'page', 'product'];
        return in_array($contentType, $allowedTypes);
    }

    /**
     * Check user permissions for scheduling
     * 
     * @param int $userId
     * @return bool True if permitted
     */
    private function checkSchedulingPermissions(int $userId): bool {
        // In a real implementation, this would check the RBAC system
        // For now, assume all users with ID > 0 can schedule
        return $userId > 0;
    }
}
