<?php

class EventManagerTask
{
    public static function run()
    {
        $logFile = __DIR__ . '/../../logs/migrations.log';
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] EventManagerTask called (not implemented)\n";

        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);

        return false;
    }
}
