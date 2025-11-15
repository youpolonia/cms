<?php
namespace Includes\Services;

use Includes\Controllers\BackupController;
use Includes\Utils\SystemStatus;

class BackupScheduler {
    private BackupController $backupController;
    private string $logFile = 'logs/backup_scheduler.log';

    public function __construct() {
        $this->backupController = new BackupController();
    }

    public function runScheduledBackup(string $frequency = 'daily'): bool {
        if ($this->shouldSkipBackup()) {
            $this->log('Skipping backup - system in maintenance mode or high load');
            return false;
        }

        try {
            $result = $this->backupController->createBackup($frequency);
            $this->log("Backup completed: " . ($result ? 'Success' : 'Failed'));
            return $result;
        } catch (\Exception $e) {
            $this->log("Backup failed: " . $e->getMessage());
            return false;
        }
    }

    private function shouldSkipBackup(): bool {
        return SystemStatus::isMaintenanceMode() || SystemStatus::getSystemLoad() > 0.7;
    }

    private function log(string $message): void {
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($this->logFile, "[$timestamp] $message\n", FILE_APPEND);
    }

    public function getLastBackupTime(): ?string {
        if (!file_exists($this->logFile)) {
            return null;
        }

        $lines = file($this->logFile);
        foreach (array_reverse($lines) as $line) {
            if (strpos($line, 'Backup completed: Success') !== false) {
                preg_match('/\[(.*?)\]/', $line, $matches);
                return $matches[1] ?? null;
            }
        }
        return null;
    }
}
