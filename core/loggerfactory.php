<?php
namespace Core;

class LoggerFactory {
    private static $logger;
    private static $fileLogger;
    private static $databaseLogger;
    private static $emergencyLogger;
    
    public static function create(string $type = 'file', array $config = []) {
        if (!self::$logger) {
            switch ($type) {
                case 'file':
                default:
                    self::$logger = new FileLogger(
                        $config['path'] ?? __DIR__.'/../logs/errors.log',
                        $config['max_size'] ?? 1048576 // 1MB
                    );
                    break;
            }
        }
        return self::$logger;
    }
    
    public static function setFileLogger($logger) {
        self::$fileLogger = $logger;
    }
    
    public static function setDatabaseLogger($logger) {
        self::$databaseLogger = $logger;
    }
    
    public static function getLogger() {
        // Try file logger first
        if (self::$fileLogger) {
            try {
                return self::$fileLogger;
            } catch (Exception $e) {
                // Fall through to next logger
            }
        }
        
        // Try database logger next
        if (self::$databaseLogger) {
            try {
                return self::$databaseLogger;
            } catch (Exception $e) {
                // Fall through to emergency logger
            }
        }
        
        // Use emergency logger as last resort
        if (!self::$emergencyLogger) {
            self::$emergencyLogger = new EmergencyLogger();
        }
        return self::$emergencyLogger;
    }
}

require_once __DIR__.'/loggerinterface.php';

class FileLogger implements LoggerInterface {
    private $logPath;
    private $maxSize;
    
    public function __construct(string $logPath, int $maxSize) {
        $this->logPath = $logPath;
        $this->maxSize = $maxSize;
        
        // Ensure directory exists
        $logDir = dirname($logPath);
        if (!file_exists($logDir)) {
            @mkdir($logDir, 0755, true);
            @file_put_contents($logDir.'/.htaccess', 'Deny from all');
        }
    }
    
    public function emergency(string $message, array $context = []) {
        $this->log('EMERGENCY', $message, $context);
    }
    
    public function alert(string $message, array $context = []) {
        $this->log('ALERT', $message, $context);
    }
    
    public function critical(string $message, array $context = []) {
        $this->log('CRITICAL', $message, $context);
    }
    
    public function error(string $message, array $context = []) {
        $this->log('ERROR', $message, $context);
    }
    
    public function warning(string $message, array $context = []) {
        $this->log('WARNING', $message, $context);
    }
    
    public function notice(string $message, array $context = []) {
        $this->log('NOTICE', $message, $context);
    }
    
    public function info(string $message, array $context = []) {
        $this->log('INFO', $message, $context);
    }
    
    public function debug(string $message, array $context = []) {
        $this->log('DEBUG', $message, $context);
    }
    
    private function log(string $level, string $message, array $context) {
        $entry = sprintf(
            "[%s] %s: %s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message
        );
        
        if (!empty($context)) {
            $entry .= "Context: " . json_encode($context) . "\n";
        }
        
        try {
            // Ensure log directory exists
            $logDir = dirname($this->logPath);
            if (!is_dir($logDir) && !mkdir($logDir, 0775, true)) {
                throw new \RuntimeException("Failed to create log directory");
            }

            // Create log file if missing
            if (!file_exists($this->logPath)) {
                if (file_put_contents($this->logPath, '') === false) {
                    throw new \RuntimeException("Failed to create log file");
                }
                chmod($this->logPath, 0664);
            }

            if (file_put_contents($this->logPath, $entry, FILE_APPEND) === false) {
                throw new \RuntimeException("Failed to write to log file");
            }
            
            // Rotate log if exceeds max size
            if (filesize($this->logPath) > $this->maxSize) {
                $this->rotateLog();
            }
        } catch (\Exception $e) {
            // Fallback to syslog if file logging fails
            syslog(LOG_ERR, "FileLogger failed: " . $e->getMessage() . " - Log entry: " . $entry);
        }
    }
    
    private function rotateLog() {
        $backup = $this->logPath . '.' . date('YmdHis');
        @rename($this->logPath, $backup);
    }
}
