<?php
/**
 * Logger factory with singleton pattern
 */
namespace Core\Logger;

require_once __DIR__ . '/loggerinterface.php';
use Core\Logger\LoggerInterface;

class LoggerFactory implements LoggerInterface
{
    // Default configuration constants
    private const DEFAULT_LOG_PATH = 'logs/app.log';
    private const DEFAULT_EMERGENCY_PATH = 'logs/emergency.log';
    private const DEFAULT_RETRIES = 1;
    private const DEFAULT_RETRY_DELAY_MS = 100;
    
    private static ?\Core\Logger\LoggerInterface $instance = null;
    private static array $config = [];
    private static string $defaultLogger = 'file';
    private static ?string $forcedLoggerType = null;
    private static bool $configLoaded = false;

    /**
     * Get logger instance (singleton)
     * 
     * @return LoggerInterface
     * @throws \RuntimeException If logger cannot be created
     */
    public static function getInstance(): LoggerInterface
    {
        if (self::$instance === null) {
            self::loadConfig();
            self::$instance = self::createLogger();
        }
        return self::$instance;
    }

    /**
     * Configure logger factory
     * 
     * @param array $config Configuration options:
     *   - type: 'file'|'database' (default: 'file')
     *   - file_path: For file logger (default: 'logs/app.log')
     *   - db_config: For database logger (see DatabaseLogger)
     */
    public static function configure(array $config): void
    {
        self::loadConfig();
        self::$config = array_merge(self::$config, $config);
        
        if (isset($config['type'])) {
            self::$defaultLogger = $config['type'];
        }

        // Reset instance to force recreation with new config
        self::$instance = null;
    }

    /**
     * Load centralized logger configuration
     */
    private static function loadConfig(): void
    {
        if (self::$configLoaded) {
            return;
        }

        $configFile = __DIR__ . '/../../../config/logger.php';
        if (file_exists($configFile)) {
            $config = require_once $configFile;
            
            // Apply environment-specific settings if available
            $env = $_ENV['APP_ENV'] ?? 'development';
            if (isset($config['environments'][$env])) {
                $config = array_merge($config, $config['environments'][$env]);
            }

            self::configure($config);
            self::$configLoaded = true;
        }
    }

    /**
     * Create new logger instance based on configuration
     * 
     * @return LoggerInterface
     * @throws \RuntimeException If logger cannot be created
     */
    private static function createLogger(): LoggerInterface
    {
        $loggerType = self::determineLoggerType();
        $emergencyConfig = [
            'path' => self::$config['fallback']['emergency_path'] ?? self::DEFAULT_EMERGENCY_PATH,
            'use_stderr' => self::$config['fallback']['use_stderr'] ?? false
        ];
        $maxRetries = self::$config['fallback']['max_retries'] ?? self::DEFAULT_RETRIES;
        $retryDelayMs = self::$config['fallback']['retry_delay_ms'] ?? self::DEFAULT_RETRY_DELAY_MS;

        try {
            if ($loggerType === 'database') {
                return self::createDatabaseLoggerWithFallback($maxRetries, $retryDelayMs);
            }
            return self::createFileLoggerWithFallback($maxRetries, $retryDelayMs, $emergencyConfig);
        } catch (\Exception $e) {
            return self::fallbackToEmergencyLogger($e, $emergencyConfig);
        }
    }

    private static function createDatabaseLoggerWithFallback(int $maxRetries, int $retryDelayMs): LoggerInterface
    {
        $lastException = null;
        
        // Try database logger with retries
        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                $logger = self::createDatabaseLogger();
                if (self::isLoggerHealthy($logger)) {
                    $logger->log('Database logger initialized successfully');
                    return $logger;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                if ($i < $maxRetries - 1) {
                    usleep($retryDelayMs * 1000);
                }
            }
        }

        // Fallback to file logger with retries
        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                $fallbackLogger = self::createFileLogger();
                if (self::isLoggerHealthy($fallbackLogger)) {
                    $fallbackLogger->log(
                        'Database logger failed, falling back to file logger: ' .
                        $lastException?->getMessage() ?? 'Unknown error'
                    );
                    return $fallbackLogger;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                if ($i < $maxRetries - 1) {
                    usleep($retryDelayMs * 1000);
                }
            }
        }

        throw $lastException ?? new \RuntimeException('All logger attempts failed');
    }

    private static function createFileLoggerWithFallback(int $maxRetries, int $retryDelayMs, array $emergencyConfig): LoggerInterface
    {
        $lastException = null;
        
        for ($i = 0; $i < $maxRetries; $i++) {
            try {
                $logger = self::createFileLogger();
                if (self::isLoggerHealthy($logger)) {
                    $logger->log('File logger initialized successfully');
                    return $logger;
                }
            } catch (\Exception $e) {
                $lastException = $e;
                if ($i < $maxRetries - 1) {
                    usleep($retryDelayMs * 1000);
                }
            }
        }

        throw $lastException ?? new \RuntimeException('File logger initialization failed');
    }

    private static function fallbackToEmergencyLogger(\Exception $e, array $emergencyConfig): LoggerInterface
    {
        $emergencyLogger = self::createEmergencyLogger($emergencyConfig);
        $emergencyLogger->log(
            'All loggers failed, using emergency logger: ' .
            $e->getMessage()
        );
        return $emergencyLogger;
    }

    private static function createDatabaseLogger(): LoggerInterface
    {
        require_once __DIR__ . '/databaselogger.php';
        return new DatabaseLogger(self::$config['db_config'] ?? []);
    }

    private static function createFileLogger(): LoggerInterface
    {
        require_once __DIR__ . '/filelogger.php';
        return new FileLogger(self::$config['file_path'] ?? 'logs/app.log');
    }

    private static function createEmergencyLogger(array $config): LoggerInterface
    {
        require_once __DIR__ . '/emergencylogger.php';
        return new EmergencyLogger(
            $config['path'],
            $config['use_stderr']
        );
    }

    private static function isLoggerHealthy(LoggerInterface $logger): bool
    {
        if (method_exists($logger, 'isHealthy')) {
            return $logger->isHealthy();
        }
        
        // Fallback for loggers without health check
        try {
            $logger->log('info', 'Logger health check (legacy)');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Determine which logger to use based on environment and config
     */
    /**
     * Force logger type for testing purposes
     * @param string|null $type 'file'|'database' or null to clear forced type
     */
    public static function setForcedLoggerType(?string $type): void
    {
        self::$forcedLoggerType = $type;
        self::$instance = null; // Reset instance to force recreation
    }

    private static function determineLoggerType(): string
    {
        // Use forced type if set (for testing)
        if (self::$forcedLoggerType !== null) {
            return self::$forcedLoggerType;
        }

        // Use explicitly configured type if set
        if (isset(self::$config['type'])) {
            return self::$config['type'];
        }

        // Default to file logger in development
        if (self::isDevelopment()) {
            return 'file';
        }

        // Default to database logger in production
        return 'database';
    }

    /**
     * Check if we're in development environment
     */
    private static function isDevelopment(): bool
    {
        return ($_SERVER['APP_ENV'] ?? 'production') === 'development';
    }

    /**
     * Prevent direct instantiation
     */
    private function __construct() {}
    private function __clone() {}
    public function __wakeup()
    {
        throw new \RuntimeException("Cannot unserialize singleton");
    }

    /**
     * System is unusable.
     */
    public function emergency(string|\Stringable $message, array $context = []): void
    {
        self::getInstance()->log('emergency', $message, $context);
    }

    /**
     * Action must be taken immediately.
     */
    public function alert(string|\Stringable $message, array $context = []): void
    {
        self::getInstance()->log('alert', $message, $context);
    }

    /**
     * Critical conditions.
     */
    public function critical(string|\Stringable $message, array $context = []): void
    {
        self::getInstance()->log('critical', $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     */
    public function error(string|\Stringable $message, array $context = []): void
    {
        self::getInstance()->log('error', $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     */
    public function warning(string|\Stringable $message, array $context = []): void
    {
        self::getInstance()->log('warning', $message, $context);
    }

    /**
     * Normal but significant events.
     */
    public function notice(string|\Stringable $message, array $context = []): void
    {
        self::getInstance()->log('notice', $message, $context);
    }

    /**
     * Interesting events.
     */
    public function info(string|\Stringable $message, array $context = []): void
    {
        self::getInstance()->log('info', $message, $context);
    }

    /**
     * Detailed debug information.
     */
    public function debug(string|\Stringable $message, array $context = []): void
    {
        self::getInstance()->log('debug', $message, $context);
    }

    /**
     * PSR-3 log level priorities (higher values = more severe)
     */
    private const LEVEL_PRIORITIES = [
        'debug' => 100,
        'info' => 200,
        'notice' => 250,
        'warning' => 300,
        'error' => 400,
        'critical' => 500,
        'alert' => 550,
        'emergency' => 600
    ];

    /**
     * Logs with an arbitrary level.
     */
    /**
     * Get normalized log level name
     */
    private static function normalizeLevel(string $level): string
    {
        return strtolower($level);
    }

    /**
     * Compare two log levels according to PSR-3 hierarchy
     *
     * @param string $incomingLevel The level of the message to log
     * @param string $thresholdLevel The minimum level that should be logged
     * @return bool True if $incomingLevel is equal or higher priority than $thresholdLevel
     */
    private static function compareLogLevels(string $incomingLevel, string $thresholdLevel): bool
    {
        $incomingLevel = self::normalizeLevel($incomingLevel);
        $thresholdLevel = self::normalizeLevel($thresholdLevel);

        if (!isset(self::LEVEL_PRIORITIES[$incomingLevel]) || !isset(self::LEVEL_PRIORITIES[$thresholdLevel])) {
            return false;
        }

        return self::LEVEL_PRIORITIES[$incomingLevel] >= self::LEVEL_PRIORITIES[$thresholdLevel];
    }

    /**
     * Check if message should be logged based on current log level
     */
    private static function shouldLog(string $messageLevel): bool
    {
        // Get component name from backtrace (last frame with class)
        $component = 'global';
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        foreach ($backtrace as $frame) {
            if (isset($frame['class'])) {
                $component = basename(str_replace('\\', '/', $frame['class']));
                break;
            }
        }

        // Check for component-specific override first
        $currentLevel = 'debug';
        if (defined('LOG_LEVEL_OVERRIDES') && is_array(LOG_LEVEL_OVERRIDES)) {
            $currentLevel = LOG_LEVEL_OVERRIDES[$component] ?? $currentLevel;
        }
        
        // Fall back to global LOG_LEVEL if no override
        if (defined('LOG_LEVEL')) {
            $currentLevel = LOG_LEVEL;
        }

        $currentLevel = self::normalizeLevel($currentLevel);
        $messageLevel = self::normalizeLevel($messageLevel);

        if (!isset(self::LEVEL_PRIORITIES[$messageLevel])) {
            return false;
        }

        return self::compareLogLevels($messageLevel, $currentLevel);
    }

    /**
     * Logs with an arbitrary level.
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $logger = self::getInstance();
        
        // Skip level check for emergency logger
        if ($logger instanceof \Core\Logger\EmergencyLogger) {
            $logger->log($level, $message, $context);
            return;
        }

        // Check if message should be logged based on level priority
        if (self::shouldLog($level)) {
            $logger->log($level, $message, $context);
        } else if (!isset(self::LEVEL_PRIORITIES[self::normalizeLevel($level)])) {
            // Unknown level - log with warning
            $logger->log('warning', "Unknown log level '$level' for message: $message", $context);
        }
    }
}
