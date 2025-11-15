<?php
class EmergencyLogger {
    public static function log(string $message, string $ip): void {
        $logDir = __DIR__ . '/../logs/emergency/';
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        $logFile = $logDir . 'emergency.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] [$ip] $message\n", FILE_APPEND);
    }
}
