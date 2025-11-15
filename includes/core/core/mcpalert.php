<?php
/**
 * MCP Alert System for cross-mode coordination
 */
class MCPAlert {
    const MEMORY_BANK_DIR = __DIR__ . '/../../logs/';
    const SYNC_LOG = __DIR__ . '/../../logs/sync.log';
    const MODES_CONFIG = __DIR__ . '/../../config/modes.json';

    /**
     * Sync memory-bank files across all modes
     */
    public static function syncFiles(): bool {
        try {
            $files = glob(self::MEMORY_BANK_DIR . '*.md');
            if ($files === false) {
                throw new RuntimeException('Failed to scan memory-bank directory');
            }

            $lastSync = file_exists(self::SYNC_LOG) ? filemtime(self::SYNC_LOG) : 0;
            
            $changedFiles = [];
            foreach ($files as $file) {
                $mtime = filemtime($file);
                if ($mtime === false) {
                    error_log("MCPAlert: Failed to get mtime for {$file}");
                    continue;
                }
                
                if ($mtime > $lastSync) {
                    $changedFiles[] = basename($file);
                }
            }

            if (!empty($changedFiles) && !self::notifyModes($changedFiles)) {
                throw new RuntimeException('Failed to notify modes about changes');
            }

            if (!touch(self::SYNC_LOG)) {
                throw new RuntimeException('Failed to update sync log timestamp');
            }

            return true;
        } catch (RuntimeException $e) {
            error_log('MCPAlert Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get available modes from config with fallback to defaults
     */
    protected static function getAvailableModes(): array {
        if (!file_exists(self::MODES_CONFIG)) {
            return ['architect', 'code', 'debug', 'documents', 'orchestrator'];
        }

        $config = json_decode(file_get_contents(self::MODES_CONFIG), true);
        return $config['modes'] ?? ['architect', 'code', 'debug', 'documents', 'orchestrator'];
    }

    /**
     * Notify all modes about file changes
     */
    protected static function notifyModes(array $filenames): bool {
        try {
            $modes = self::getAvailableModes();
            if (empty($modes)) {
                throw new RuntimeException('No available modes found');
            }

            $timestamp = date('Y-m-d H:i:s');
            $success = true;
            
            foreach ($modes as $mode) {
                $content = '';
                foreach ($filenames as $filename) {
                    $content .= "Update: {$filename} changed at {$timestamp}" . PHP_EOL;
                }

                $bytes = file_put_contents(
                    self::MEMORY_BANK_DIR . "{$mode}_notification.md",
                    $content,
                    FILE_APPEND | LOCK_EX
                );

                if ($bytes === false) {
                    throw new RuntimeException("Failed to write notification for {$mode}");
                }
            }

            return true;
        } catch (RuntimeException $e) {
            error_log('MCPAlert Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check for dependency conflicts
     */
    public static function checkDependencies() {
        $dependencies = file_get_contents(self::MEMORY_BANK_DIR . 'dependencies.md');
        // Parse dependencies and verify requirements
        return true; // Simplified for this example
    }

    /**
     * Conflict resolution protocol
     */
    public static function resolveConflict(string $conflictType, array $parties) {
        $resolution = [
            'timestamp' => date('Y-m-d H:i:s'),
            'conflict' => $conflictType,
            'parties' => $parties,
            'resolution' => 'Pending orchestration'
        ];

        file_put_contents(
            self::MEMORY_BANK_DIR . 'conflicts.log',
            json_encode($resolution) . PHP_EOL,
            FILE_APPEND
        );

        return $resolution;
    }

    /**
     * Log an alert with severity level
     *
     * @param string $title Alert title
     * @param string $message Alert message
     * @param string $severity Severity level (INFO, WARNING, CRITICAL)
     * @return bool True if alert was logged successfully
     */
    public function logAlert(string $title, string $message, string $severity = 'INFO'): bool
    {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$severity] $title - $message" . PHP_EOL;
        
        $logFile = self::MEMORY_BANK_DIR . 'alerts.log';
        return file_put_contents($logFile, $logEntry, FILE_APPEND) !== false;
    }

    /**
     * @deprecated Use logAlert() instead
     */
    public function send(string $title, string $message): bool
    {
        return $this->logAlert($title, $message, 'INFO');
    }

    /**
     * @deprecated Use logAlert() instead
     */
    public function sendCritical(string $title, string $message): bool
    {
        return $this->logAlert($title, $message, 'CRITICAL');
    }
}
