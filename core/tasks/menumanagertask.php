<?php

namespace core\tasks;

require_once __DIR__ . '/../logger.php';

use core\Logger;

class MenuManagerTask
{
    public static function run(): bool
    {
        $timestamp = date('Y-m-d H:i:s');
        $message = "[{$timestamp}] MenuManagerTask called (not implemented)";

        Logger::log($message, 'migrations.log');

        return false;
    }
}
