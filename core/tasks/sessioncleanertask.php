<?php
namespace core\tasks;

class SessionCleanerTask {
    public static function run(): bool {
        // Placeholder implementation - logs call and returns false
        $logMessage = '[' . date('Y-m-d H:i:s') . '] SessionCleanerTask called (not implemented)';
        \error_log($logMessage . PHP_EOL, 3, __DIR__ . '/../../logs/migrations.log');
        
        return false;
    }
}
