<?php

class SchedulingController {
    private $db;

    public function __construct() {
        global $db;
        $this->db = $db;
    }

    /**
     * Create a new content schedule
     * @param array $input POST data
     * @return array API response
     */
    public function create(array $input): array {
        // Validate input
        $errors = $this->validateScheduleData($input);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check for conflicts
        if ($this->hasScheduleConflict($input)) {
            return ['success' => false, 'error' => 'Schedule conflict detected'];
        }

        // Insert schedule
        $stmt = $this->db->prepare("INSERT INTO scheduled_content 
            (content_id, publish_at, status, created_by) 
            VALUES (?, ?, 'pending', ?)");
        $stmt->execute([
            $input['content_id'],
            $input['publish_at'],
            $input['user_id']
        ]);

        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    /**
     * List all schedules
     * @return array API response
     */
    public function list(): array {
        $stmt = $this->db->query("SELECT * FROM scheduled_content ORDER BY publish_at DESC");
        return ['success' => true, 'data' => $stmt->fetchAll()];
    }

    /**
     * Get a specific schedule
     * @param int $id Schedule ID
     * @return array API response
     */
    public function get(int $id): array {
        $stmt = $this->db->prepare("SELECT * FROM scheduled_content WHERE id = ?");
        $stmt->execute([$id]);
        $schedule = $stmt->fetch();

        if (!$schedule) {
            return ['success' => false, 'error' => 'Schedule not found'];
        }

        return ['success' => true, 'data' => $schedule];
    }

    /**
     * Update a schedule
     * @param int $id Schedule ID
     * @param array $input PUT data
     * @return array API response
     */
    public function update(int $id, array $input): array {
        // Validate input
        $errors = $this->validateScheduleData($input);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        // Check for conflicts (excluding current schedule)
        if ($this->hasScheduleConflict($input, $id)) {
            return ['success' => false, 'error' => 'Schedule conflict detected'];
        }

        // Update schedule
        $stmt = $this->db->prepare("UPDATE scheduled_content 
            SET content_id = ?, publish_at = ?, updated_at = NOW() 
            WHERE id = ?");
        $stmt->execute([
            $input['content_id'],
            $input['publish_at'],
            $id
        ]);

        return ['success' => true];
    }

    /**
     * Cancel a schedule
     * @param int $id Schedule ID
     * @return array API response
     */
    public function cancel(int $id): array {
        $stmt = $this->db->prepare("UPDATE scheduled_content 
            SET status = 'cancelled', updated_at = NOW() 
            WHERE id = ?");
        $stmt->execute([$id]);

        return ['success' => true];
    }

    /**
     * Validate schedule data
     * @param array $data Input data
     * @return array Validation errors
     */
    private function validateScheduleData(array $data): array {
        $errors = [];
        
        if (empty($data['content_id'])) {
            $errors['content_id'] = 'Content ID is required';
        }

        if (empty($data['publish_at'])) {
            $errors['publish_at'] = 'Publish date/time is required';
        } elseif (!strtotime($data['publish_at'])) {
            $errors['publish_at'] = 'Invalid publish date/time format';
        }

        if (empty($data['user_id'])) {
            $errors['user_id'] = 'User ID is required';
        }

        return $errors;
    }

    /**
     * Check for schedule conflicts
     * @param array $data Schedule data
     * @param int|null $excludeId Schedule ID to exclude from conflict check
     * @return bool Whether conflict exists
     */
    private function hasScheduleConflict(array $data, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) FROM scheduled_content 
            WHERE content_id = ? 
            AND publish_at = ? 
            AND status = 'pending'";
        
        $params = [$data['content_id'], $data['publish_at']];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn() > 0;
    }
}
