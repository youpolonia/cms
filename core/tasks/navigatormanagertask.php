<?php

namespace core\tasks;

require_once __DIR__ . '/../logger.php';

use core\Logger;

class NavigatorManagerTask
{
    public static function run(): bool
    {
        $timestamp = date('Y-m-d H:i:s');
        $message = "[{$timestamp}] NavigatorManagerTask called (not implemented)";

        Logger::log($message, 'migrations.log');

        return false;
    }
}
