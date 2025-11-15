<?php

class ScheduleNotification {
    const TYPE_CREATED = 'schedule_created';
    const TYPE_UPDATED = 'schedule_updated';
    const TYPE_EXECUTING = 'schedule_executing';
    const TYPE_COMPLETED = 'schedule_completed';
    const TYPE_CONFLICT = 'schedule_conflict';

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($userId, $scheduleId, $type, $data) {
        $query = "INSERT INTO schedule_notifications 
                 (user_id, schedule_id, type, data, created_at) 
                 VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $userId,
            $scheduleId,
            $type,
            json_encode($data)
        ]);

        return $this->db->lastInsertId();
    }

    public function getForUser($userId, $limit = 10) {
        $query = "SELECT * FROM schedule_notifications 
                 WHERE user_id = ? 
                 ORDER BY created_at DESC 
                 LIMIT ?";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([$userId, $limit]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($notificationId) {
        $query = "UPDATE schedule_notifications 
                 SET read_at = NOW() 
                 WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        return $stmt->execute([$notificationId]);
    }
}
