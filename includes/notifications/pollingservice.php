<?php
require_once __DIR__ . '/../../core/database.php';

class PollingService {
    const DEFAULT_INTERVAL = 30; // seconds
    
    private $interval;
    private $lastPollTime = 0;
    private $isPolling = false;
    
    public function __construct(int $interval = self::DEFAULT_INTERVAL) {
        $this->interval = $interval;
    }
    
    public function poll(callable $callback): void {
        if ($this->isPolling) {
            return;
        }
        
        $this->isPolling = true;
        
        try {
            $currentTime = time();
            
            // Only poll if interval has elapsed
            if (($currentTime - $this->lastPollTime) >= $this->interval) {
                $this->lastPollTime = $currentTime;
                $callback($this->getNewNotifications());
            }
        } finally {
            $this->isPolling = false;
        }
    }
    
    public function getStatus(): array {
        return [
            'is_active' => $this->isPolling,
            'last_poll' => $this->lastPollTime,
            'next_poll' => $this->lastPollTime + $this->interval,
            'interval' => $this->interval
        ];
    }
    
    private function getNewNotifications(): array {
        // Efficient query to get only new notifications since last poll
        $pdo = \core\Database::connection();
        $query = "SELECT * FROM notifications 
                 WHERE created_at > FROM_UNIXTIME(?) 
                 AND is_read = 0 
                 ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$this->lastPollTime]);
        return $stmt->fetchAll();
    }
}
