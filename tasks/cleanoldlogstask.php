<?php
/**
 * Task to clean old log files
 */
class CleanOldLogsTask {
    private $tenantId;
    private $logger;

    public function __construct($tenantId) {
        $this->tenantId = $tenantId;
        $this->logger = new TaskLogger();
    }

    public function execute() {
        $this->logger->log("Starting CleanOldLogsTask for tenant {$this->tenantId}");
        
        try {
            $logDir = "logs/tenant_{$this->tenantId}";
            $daysToKeep = 30;
            $filesDeleted = 0;
            
            if (is_dir($logDir)) {
                foreach (glob("$logDir/*.log") as $file) {
                    if (filemtime($file) < time() - ($daysToKeep * 86400)) {
                        unlink($file);
                        $filesDeleted++;
                    }
                }
            }
            
            $this->logger->log("Completed CleanOldLogsTask. Deleted {$filesDeleted} old log files");
            return true;
        } catch (Exception $e) {
            $this->logger->log("CleanOldLogsTask failed: " . $e->getMessage());
            return false;
        }
    }
}

class TaskLogger {
    public function log($message) {
        $logEntry = date('Y-m-d H:i:s') . " - " . $message . PHP_EOL;
        file_put_contents('tasks/task_log.txt', $logEntry, FILE_APPEND);
    }
}
