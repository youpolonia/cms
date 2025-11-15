<?php

class SchedulerManagerTask
{
    public static function run()
    {
        $logFile = dirname(__DIR__, 2) . '/logs/scheduler_manager.log';

        // Check if rotation is needed (1MB = 1048576 bytes)
        if (file_exists($logFile) && filesize($logFile) >= 1048576) {
            $rotatedFile = $logFile . '.1';
            rename($logFile, $rotatedFile);
        }

        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $logEntry = $timestamp . " SchedulerManagerTask executed\n";

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        return true;
    }
}
