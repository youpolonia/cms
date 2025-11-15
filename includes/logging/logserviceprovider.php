<?php

namespace CMS\Logging;

class LogServiceProvider
{
    public static function register(): void
    {
        $config = require_once __DIR__ . '/../../config/logging.php';
        
        // Set default log path from config
        if (isset($config['default_path'])) {
            Logger::setLogPath($config['default_path']);
        }

        // Set default channel from config
        if (isset($config['default'])) {
            Logger::setDefaultChannel($config['default']);
        }
    }

    public static function getLogger($channel = null)
    {
        require_once __DIR__ . '/../../core/logger/LoggerFactory.php';
        $config = require_once __DIR__ . '/../../config/logging.php';
        return LoggerFactory::create($config['default'] ?? 'file', [
            'channel' => $channel,
            'file_path' => $config['default_path'] ?? __DIR__ . '/../../logs/app.log'
        ]);
    }
}
