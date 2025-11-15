<?php
namespace core;
require_once __DIR__ . '/errorhandler.php';
/**
 * Centralized error handling for AI operations
 */
class AIErrorHandler extends ErrorHandler {
    protected static $logFile = __DIR__.'/../logs/ai_errors.log';

    /**
     * Set log file path
     */
    public static function setLogFile(string $path): void {
        self::$logFile = $path;
    }

    /**
     * Set minimum log level
     */
    public static function setLogLevel(string $level): void {
        if (!in_array($level, self::LOG_LEVELS)) {
            throw new \Exception("Invalid log level: $level");
        }
        self::$logLevel = $level;
    }

    /**
     * Handle and log AI operation errors
     */
    public static function handle(
        \Exception $e,
        string $level = 'ERROR',
        array $context = []
    ): array {
        // Add AI-specific metrics
        $context['ai_metrics'] = [
            'token_usage' => $context['token_usage'] ?? 0,
            'response_time' => $context['response_time'] ?? 0,
            'model' => $context['model'] ?? 'unknown'
        ];

        $result = parent::handle($e, $level, $context);

        // Additional AI-specific logging
        if (array_search($level, self::LOG_LEVELS) >=
            array_search(self::$logLevel, self::LOG_LEVELS)) {
            $aiLogFile = __DIR__.'/../logs/ai_metrics.log';
            $logEntry = json_encode([
                'timestamp' => date('Y-m-d H:i:s'),
                'error_id' => $result['error']['id'],
                'metrics' => $context['ai_metrics']
            ]) . "\n";
            file_put_contents($aiLogFile, $logEntry, FILE_APPEND);
        }

        return $result;
    }

    /**
     * Get recent log entries
     */
    public static function getLogs(int $limit = 100): array {
        if (!file_exists(self::$logFile)) {
            return [];
        }

        $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES);
        return array_slice($lines, -$limit);
    }

    /**
     * Clear/rotate the log file
     * @return bool True if successful, false if failed
     */
    public static function clearLogs(): bool {
        if (!file_exists(self::$logFile)) {
            return true;
        }
        
        try {
            file_put_contents(self::$logFile, '');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
