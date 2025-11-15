<?php

namespace CMS\Logging;

class Logger
{
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';

    protected static $config;
    protected static $initialized = false;

    public static function init()
    {
        if (!self::$initialized) {
            self::$config = require_once __DIR__ . '/../../config/logging.php';
            self::$initialized = true;
            
            // Ensure logs directory exists
            $logsDir = __DIR__ . '/../../storage/logs';
            if (!is_dir($logsDir)) {
                mkdir($logsDir, 0755, true);
            }
        }
    }

    public static function log(string $level, string $message, array $context = [], string $channel = null)
    {
        self::init();
        
        $channel = $channel ?? self::$config['default'] ?? 'app';
        $channelConfig = self::$config['channels'][$channel] ?? [];
        
        $logEntry = [
            'timestamp' => date('c'),
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'channel' => $channel
        ];

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $logEntry['ip'] = $_SERVER['REMOTE_ADDR'];
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $logEntry['uri'] = $_SERVER['REQUEST_URI'];
        }

        $logFile = $channelConfig['path'] ?? __DIR__ . '/../../storage/logs/' . $channel . '.log';
        $logLine = json_encode($logEntry) . PHP_EOL;

        file_put_contents($logFile, $logLine, FILE_APPEND);
    }

    public static function debug(string $message, array $context = [], string $channel = null)
    {
        self::log(self::DEBUG, $message, $context, $channel);
    }

    public static function info(string $message, array $context = [], string $channel = null)
    {
        self::log(self::INFO, $message, $context, $channel);
    }

    public static function warning(string $message, array $context = [], string $channel = null)
    {
        self::log(self::WARNING, $message, $context, $channel);
    }

    public static function error(string $message, array $context = [], string $channel = null)
    {
        self::log(self::ERROR, $message, $context, $channel);
    }

    public static function critical(string $message, array $context = [], string $channel = null)
    {
        self::log(self::CRITICAL, $message, $context, $channel);
    }
}
