<?php

class TemplateManagerTask
{
    public static function run()
    {
        $logEntry = '[' . date('Y-m-d H:i:s') . '] TemplateManagerTask called (not implemented)';

        $logFile = __DIR__ . '/../../logs/migrations.log';
        file_put_contents($logFile, $logEntry . PHP_EOL, FILE_APPEND | LOCK_EX);

        return false;
    }
}
