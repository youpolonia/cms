<?php

class VersionedScheduleService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function scheduleWithVersion($contentId, $versionId, DateTime $scheduledAt, array $recurrence = null) {
        // Check for conflicts first
        $conflicts = $this->detectConflicts($contentId, $scheduledAt, $recurrence);
        
        if (!empty($conflicts)) {
            return [
                'success' => false,
                'conflicts' => $conflicts
            ];
        }

        // Insert the scheduled event
        $stmt = $this->db->prepare("
            INSERT INTO scheduled_events 
            (content_id, version_id, scheduled_at) 
            VALUES (?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $contentId,
            $versionId,
            $scheduledAt->format('Y-m-d H:i:s')
        ]);

        if ($recurrence) {
            $this->createRecurringEvents($contentId, $versionId, $scheduledAt, $recurrence);
        }

        return ['success' => $success];
    }

    private function detectConflicts($contentId, DateTime $scheduledAt, $recurrence = null) {
        $conflicts = [];
        
        // Check for existing events at same time
        $stmt = $this->db->prepare("
            SELECT id, scheduled_at 
            FROM scheduled_events 
            WHERE content_id = ? 
            AND scheduled_at BETWEEN ? AND ?
        ");
        
        $windowStart = (clone $scheduledAt)->modify('-1 hour');
        $windowEnd = (clone $scheduledAt)->modify('+1 hour');
        
        $stmt->execute([
            $contentId,
            $windowStart->format('Y-m-d H:i:s'),
            $windowEnd->format('Y-m-d H:i:s')
        ]);
        
        if ($stmt->rowCount() > 0) {
            $conflicts[] = [
                'type' => 'time_conflict',
                'message' => 'Another event is already scheduled near this time',
                'resolution_suggestion' => 'Choose a different time or cancel the conflicting event'
            ];
        }

        // Additional conflict checks would go here
        
        return $conflicts;
    }

    private function createRecurringEvents($contentId, $versionId, DateTime $startTime, array $recurrence) {
        // Implementation for recurring events
        // Would create multiple scheduled events based on pattern
    }

    public function getUpcomingEvents($limit = 10) {
        $stmt = $this->db->prepare("
            SELECT * FROM scheduled_events
            WHERE scheduled_at > NOW()
            ORDER BY scheduled_at ASC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function updateSchedule(array $ids, array $data) {
        $this->db->beginTransaction();
        try {
            foreach ($ids as $id) {
                $conflicts = $this->detectConflicts($data['content_id'], new DateTime($data['scheduled_at']));
                if (!empty($conflicts)) {
                    throw new Exception("Conflict detected for event $id");
                }

                $stmt = $this->db->prepare("
                    UPDATE scheduled_events
                    SET scheduled_at = ?, version_id = ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $data['scheduled_at'],
                    $data['version_id'],
                    $id
                ]);
            }
            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function deleteSchedule(array $ids) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                DELETE FROM scheduled_events
                WHERE id = ?
            ");
            foreach ($ids as $id) {
                $stmt->execute([$id]);
            }
            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function changeStatus(array $ids, string $status) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                UPDATE scheduled_events
                SET status = ?
                WHERE id = ?
            ");
            foreach ($ids as $id) {
                $stmt->execute([$status, $id]);
            }
            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
