<?php

namespace Includes\Services;

class SchedulingService {
    protected $db;

    public function __construct() {
        $this->db = $this->createDatabaseConnection();
    }

    protected function createDatabaseConnection() {
        require_once __DIR__ . '/../../core/database.php';
        return \core\Database::connection();
    }

    public function createScheduledEvent($contentId, $versionId, $scheduledAt, $priority = 5) {
        // Validate content and version exist
        if (!$this->validateContentVersion($contentId, $versionId)) {
            throw new Exception("Invalid content or version");
        }

        $stmt = $this->db->prepare("
            INSERT INTO scheduled_events
            (content_id, version_id, scheduled_at, user_id, status, priority)
            VALUES (?, ?, ?, ?, 'pending', ?)
        ");

        $userId = $_SESSION['user_id'] ?? null;
        $stmt->execute([$contentId, $versionId, $scheduledAt, $userId, $priority]);

        return $this->getEventById($this->db->lastInsertId());
    }

    public function getScheduledEvents($filters = [], $orderBy = null) {
        $query = "SELECT * FROM scheduled_events WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $query .= " AND status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $query .= " AND scheduled_at >= ?";
            $params[] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $query .= " AND scheduled_at <= ?";
            $params[] = $filters['end_date'];
        }

        $query .= $orderBy ? " ORDER BY " . $orderBy : " ORDER BY scheduled_at ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateScheduledEvent($id, $scheduledAt = null, $status = null) {
        $event = $this->getEventById($id);
        if (!$event) {
            throw new Exception("Event not found");
        }

        $updates = [];
        $params = [];

        if ($scheduledAt) {
            $updates[] = "scheduled_at = ?";
            $params[] = $scheduledAt;
        }

        if ($status) {
            $updates[] = "status = ?";
            $params[] = $status;
        }

        if (empty($updates)) {
            return $event;
        }

        $query = "UPDATE scheduled_events SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $this->getEventById($id);
    }

    public function cancelScheduledEvent($id) {
        return $this->updateScheduledEvent($id, null, 'failed');
    }

    public function processDueEvents() {
        // Get pending events due now, ordered by priority (highest first)
        $dueEvents = $this->getScheduledEvents([
            'status' => 'pending',
            'end_date' => date('Y-m-d H:i:s')
        ], 'priority DESC');

        foreach ($dueEvents as $event) {
            try {
                $workerId = $this->assignToAvailableWorker($event['id']);
                if ($workerId) {
                    $this->publishEvent($event);
                }
            } catch (Exception $e) {
                $this->updateScheduledEvent($event['id'], null, 'failed');
                error_log("Failed to process event {$event['id']}: " . $e->getMessage());
            }
        }
    }

    protected function assignToAvailableWorker($eventId) {
        // Find worker with lowest current workload
        $workerId = Worker::getNextAvailableWorker();
        if (!$workerId) {
            return false;
        }
        $worker = ['worker_id' => $workerId];

        if (!$worker) {
            return false;
        }

        // Assign event to worker
        $this->db->beginTransaction();
        try {
            // Update worker's current workload
            $this->db->prepare("
                UPDATE workers
                SET current_workload = current_workload + 1
                WHERE worker_id = ?
            ")->execute([$worker['worker_id']]);

            // Create assignment record
            $this->db->prepare("
                INSERT INTO worker_assignments
                (worker_id, event_id, status)
                VALUES (?, ?, 'pending')
            ")->execute([$worker['worker_id'], $eventId]);

            // Update event with assigned worker
            $this->db->prepare("
                UPDATE scheduled_events
                SET assigned_worker_id = ?
                WHERE id = ?
            ")->execute([$worker['worker_id'], $eventId]);

            $this->db->commit();
            return $worker['worker_id'];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    protected function getEventById($id) {
        $stmt = $this->db->prepare("SELECT * FROM scheduled_events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    protected function validateContentVersion($contentId, $versionId) {
        $stmt = $this->db->prepare("
            SELECT 1 FROM content_versions
            WHERE id = ? AND content_id = ?
            LIMIT 1
        ");
        $stmt->execute([$versionId, $contentId]);
        return (bool)$stmt->fetchColumn();
    }

    protected function publishEvent($event) {
        $this->db->beginTransaction();
        try {
            // Update content to specified version
            $stmt = $this->db->prepare("
                UPDATE contents
                SET published_version_id = ?
                WHERE id = ?
            ");
            $stmt->execute([$event['version_id'], $event['content_id']]);

            // Update event status
            $this->updateScheduledEvent($event['id'], null, 'published');

            // Mark assignment as completed
            if ($event['assigned_worker_id']) {
                $this->db->prepare("
                    UPDATE worker_assignments
                    SET status = 'completed',
                        completed_at = NOW()
                    WHERE worker_id = ? AND event_id = ?
                ")->execute([$event['assigned_worker_id'], $event['id']]);

                // Decrement worker's workload
                $this->db->prepare("
                    UPDATE workers
                    SET current_workload = GREATEST(0, current_workload - 1)
                    WHERE worker_id = ?
                ")->execute([$event['assigned_worker_id']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->updateScheduledEvent($event['id'], null, 'failed');
            throw $e;
        }
    }
}
