<?php
namespace core\tasks;

class LogRotationTask {
    public static function run(): bool {
        $logFiles = [
            __DIR__ . '/../../logs/app_errors.log',
            __DIR__ . '/../../logs/php_errors.log'
        ];
        
        $success = true;
        $rotatedFiles = [];
        
        foreach ($logFiles as $logFile) {
            try {
                // Check if file exists and is over 1MB
                if (@file_exists($logFile) && @filesize($logFile) > 1048576) {
                    $rotatedFile = $logFile . '.1';
                    
                    // Remove existing .1 file if it exists
                    if (@file_exists($rotatedFile)) {
                        @unlink($rotatedFile);
                    }
                    
                    // Rotate: rename current log to .1
                    if (@rename($logFile, $rotatedFile)) {
                        // Create new empty log file
                        if (@file_put_contents($logFile, '') !== false) {
                            $rotatedFiles[] = basename($logFile);
                        } else {
                            $success = false;
                        }
                    } else {
                        $success = false;
                    }
                }
            } catch (\Throwable $e) {
                $success = false;
            }
        }
        
        // Log the execution result
        $status = $success ? 'executed ok' : 'executed failed';
        $rotatedList = empty($rotatedFiles) ? 'none' : implode(',', $rotatedFiles);
        $logMessage = '[' . date('Y-m-d H:i:s') . '] LogRotationTask ' . $status . ' (rotated=' . $rotatedList . ')';
        \error_log($logMessage . PHP_EOL, 3, __DIR__ . '/../../logs/migrations.log');
        
        return $success;
    }
}
