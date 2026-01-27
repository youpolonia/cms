<?php
require_once __DIR__ . '/../core/csrf.php';
csrf_boot('admin');

/**
 * Maintenance Scheduler - Handles time-based maintenance mode scheduling
 */
class MaintenanceScheduler {
    private $db;
    private $table = 'maintenance_schedules';
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new maintenance schedule
     */
    public function createSchedule(array $data): array {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') { csrf_validate_or_403(); }
        $this->validateScheduleData($data);
        $this->checkForConflicts($data);
        
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} 
            (start_time, end_time, reason, is_active, created_at) 
            VALUES (?, ?, ?, 0, NOW())
        ");
        
        $stmt->execute([
            $data['start_time'],
            $data['end_time'],
            $data['reason'] ?? 'Scheduled maintenance'
        ]);
        
        return ['id' => $this->db->lastInsertId(), 'status' => 'scheduled'];
    }
    
    /**
     * Validate schedule data
     */
    private function validateScheduleData(array $data): void {
        if (empty($data['start_time']) || empty($data['end_time'])) {
            throw new InvalidArgumentException('Start and end times are required');
        }
        
        $start = strtotime($data['start_time']);
        $end = strtotime($data['end_time']);
        
        if ($start === false || $end === false) {
            throw new InvalidArgumentException('Invalid date format');
        }
        
        if ($start >= $end) {
            throw new InvalidArgumentException('End time must be after start time');
        }
        
        if ($start < time()) {
            throw new InvalidArgumentException('Start time cannot be in the past');
        }
    }
    
    /**
     * Check for overlapping schedules
     */
    private function checkForConflicts(array $data): void {
        $stmt = $this->db->prepare("
            SELECT id FROM {$this->table} 
            WHERE (
                (start_time <= ? AND end_time >= ?) OR
                (start_time <= ? AND end_time >= ?) OR
                (start_time >= ? AND end_time <= ?)
            ) AND is_active != 2
        ");
        
        $stmt->execute([
            $data['start_time'], $data['start_time'],
            $data['end_time'], $data['end_time'],
            $data['start_time'], $data['end_time']
        ]);
        
        if ($stmt->rowCount() > 0) {
            throw new RuntimeException('Schedule conflicts with existing maintenance period');
        }
    }
    
    /**
     * Check and update maintenance status based on current time
     */
    public function checkScheduledMaintenance(): void {
        $now = date('Y-m-d H:i:s');
        
        // Activate upcoming schedules
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET is_active = 1 
            WHERE start_time <= ? AND end_time > ? AND is_active = 0
        ");
        $stmt->execute([$now, $now]);
        
        // Deactivate expired schedules
        $stmt = $this->db->prepare("
            UPDATE {$this->table} 
            SET is_active = 2 
            WHERE end_time <= ? AND is_active = 1
        ");
        $stmt->execute([$now]);
    }
    
    /**
     * Get active maintenance schedules
     */
    public function getActiveSchedules(): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE is_active = 1
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Get upcoming maintenance schedules
     */
    public function getUpcomingSchedules(): array {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table} 
            WHERE start_time > NOW() AND is_active = 0
            ORDER BY start_time ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Manual trigger for cron job
     */
    public function manualTrigger(): void {
        $this->checkScheduledMaintenance();
    }
}
