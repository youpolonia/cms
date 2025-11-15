<?php

class AccessLog {
    private static $logFile = __DIR__ . '/../../logs/access-denied.log';
    
    public static function logDeniedAccess(int $userId, string $permissionId, ?int $siteId = null): void {
        $timestamp = date('Y-m-d H:i:s');
        $siteContext = $siteId ? "site:$siteId" : "global";
        $logEntry = "[$timestamp] DENIED - User:$userId attempted $permissionId ($siteContext)\n";
        
        // Ensure logs directory exists
        if (!file_exists(dirname(self::$logFile))) {
            mkdir(dirname(self::$logFile), 0755, true);
        }
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }
}
