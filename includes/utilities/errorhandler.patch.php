<?php
/**
 * Enhanced Error Handler Patch
 * 
 * This patch improves how ErrorHandler handles namespace/autoloading issues by:
 * 1. Adding specific error categorization for namespace/autoloading issues
 * 2. Improving error logging with more detailed information
 * 3. Adding suggestions for fixing common namespace/autoloading issues
 */

class ErrorHandler {
    // Existing properties
    protected static $logFile = 'debug.log';
    protected static $logLevels = E_ALL;
    protected static $debugMode = false;
    protected static $logToFile = true;
    protected static $logToSyslog = false;
    
    // New properties for error categorization
    protected static $errorCategories = [
        'AUTOLOAD' => 'Autoloading Error',
        'NAMESPACE' => 'Namespace Error',
        'ROUTING' => 'Routing Error',
        'DATABASE' => 'Database Error',
        'SECURITY' => 'Security Error',
        'GENERAL' => 'General Error'
    ];
    
    // Error patterns for categorization
    protected static $errorPatterns = [
        'AUTOLOAD' => [
            '/class not found/i',
            '/failed to open stream/i',
            '/failed opening required/i'
        ],
        'NAMESPACE' => [
            '/undefined namespace/i',
            '/namespace not found/i',
            '/undefined class/i'
        ],
        'ROUTING' => [
            '/route not found/i',
            '/controller not found/i',
            '/action not found/i'
        ]
    ];
    
    // Constants for error IDs
    const UNKNOWN_ERROR_ID = 'ERR-UNKNOWN';
    const AUTOLOAD_ERROR_PREFIX = 'ERR-AL';
    const NAMESPACE_ERROR_PREFIX = 'ERR-NS';
    
    public static function register(bool $debugMode = false): void {
        if (!defined('CMS_ROOT')) {
            throw new RuntimeException('CMS_ROOT must be defined before registering ErrorHandler');
        }
        self::$debugMode = $debugMode;
        self::$logFile = \CMS_ROOT . '/logs/debug.log'; // Set absolute path for log file
        // Ensure logs directory exists
        if (!is_dir(\CMS_ROOT . '/logs')) {
            mkdir(\CMS_ROOT . '/logs', 0755, true);
        }
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }
    
    public static function handleError(int $errno, string $errstr, string $errfile, int $errline): bool {
        if (!(self::$logLevels & $errno)) {
            return false;
        }

        $category = self::categorizeError($errstr);
        $errorId = self::generateErrorId($category);
        $message = sprintf(
            "[%s] [%s] [%s] %s (%d): %s in %s on line %d",
            date('Y-m-d H:i:s'),
            $errorId,
            $category,
            self::getErrorType($errno),
            $errno,
            $errstr,
            $errfile,
            $errline
        );

        self::log($message);
        
        if (self::$debugMode) {
            self::displayDebugError($errno, $errstr, $errfile, $errline, $category, $errorId);
        }
        
        return true;
    }
    
    public static function handleException(Throwable $e): void {
        $category = self::categorizeException($e);
        $errorId = self::generateErrorId($category);
        $message = sprintf(
            "[%s] [%s] [%s] Exception: %s in %s on line %d\nStack Trace:\n%s",
            date('Y-m-d H:i:s'),
            $errorId,
            $category,
            $e->getMessage(),
            $e->getFile(),
            $e->getLine(),
            $e->getTraceAsString()
        );

        // Add suggestions for common errors
        $suggestions = self::getSuggestionsForException($e, $category);
        if (!empty($suggestions)) {
            $message .= "\n\nSuggestions:\n" . implode("\n", $suggestions);
        }

        // Log to both debug.log and system.log
        self::log($message);
        file_put_contents(\CMS_ROOT . '/logs/system.log', $message . PHP_EOL, FILE_APPEND);
        
        if (self::$debugMode) {
            self::displayDebugException($e, $category, $errorId, $suggestions);
        } else {
            http_response_code(500);
            require_once \CMS_ROOT . '/templates/error.php';
            exit;
        }
    }
    
    /**
     * Categorize an error message
     */
    protected static function categorizeError(string $errstr): string {
        foreach (self::$errorPatterns as $category => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $errstr)) {
                    return $category;
                }
            }
        }
        return 'GENERAL';
    }
    
    /**
     * Categorize an exception
     */
    protected static function categorizeException(Throwable $e): string {
        $message = $e->getMessage();
        
        // Check for class not found errors (autoloading issues)
        if (strpos($message, 'Class') !== false && strpos($message, 'not found') !== false) {
            return 'AUTOLOAD';
        }
        
        // Check for namespace errors
        if (strpos($message, 'namespace') !== false || 
            (strpos($message, 'Undefined') !== false && strpos($message, 'class') !== false)) {
            return 'NAMESPACE';
        }
        
        // Check for routing errors
        if ($e instanceof \RuntimeException && 
            (strpos($message, 'route') !== false || strpos($message, 'controller') !== false)) {
            return 'ROUTING';
        }
        
        return 'GENERAL';
    }
    
    /**
     * Generate suggestions for common exceptions
     */
    protected static function getSuggestionsForException(Throwable $e, string $category): array {
        $suggestions = [];
        $message = $e->getMessage();
        
        switch ($category) {
            case 'AUTOLOAD':
                // Extract class name from error message
                if (preg_match('/Class \'([^\']+)\'/', $message, $matches)) {
                    $className = $matches[1];
                    $suggestions[] = "Class '{$className}' could not be found by the autoloader.";
                    $suggestions[] = "Check if the file exists and has the correct namespace declaration.";
                    $suggestions[] = "Verify that the directory structure matches the namespace structure (case-sensitive).";
                    /* (clean) */
                }
                break;
                
            case 'NAMESPACE':
                $suggestions[] = "Namespace error detected. Check for:";
                $suggestions[] = "- Mismatched namespace declarations";
                $suggestions[] = "- Case sensitivity issues between directory names and namespaces";
                $suggestions[] = "- Missing 'use' statements for required classes";
                break;
                
            case 'ROUTING':
                $suggestions[] = "Routing error detected. Check for:";
                $suggestions[] = "- Missing or incorrectly defined routes";
                $suggestions[] = "- Controller class not found or not properly autoloaded";
                $suggestions[] = "- Method not found in controller class";
                break;
        }
        
        return $suggestions;
    }
    
    /**
     * Generate an error ID based on the category
     */
    public static function generateErrorId(string $category = 'GENERAL'): string {
        // Primary method - uniqid with entropy
        try {
            $prefix = match($category) {
                'AUTOLOAD' => self::AUTOLOAD_ERROR_PREFIX,
                'NAMESPACE' => self::NAMESPACE_ERROR_PREFIX,
                default => 'ERR'
            };
            
            $id = $prefix . '-' . substr(uniqid('', true), -8);
            if (preg_match('/^[A-Z]+-[a-zA-Z0-9-]{8,12}$/', $id)) {
                return $id;
            }
        } catch (Throwable $e) {
            // Fall through to secondary method
        }

        // Secondary method - random_bytes
        try {
            $prefix = match($category) {
                'AUTOLOAD' => self::AUTOLOAD_ERROR_PREFIX,
                'NAMESPACE' => self::NAMESPACE_ERROR_PREFIX,
                default => 'ERR'
            };
            
            $bytes = random_bytes(4);
            return $prefix . '-' . bin2hex($bytes);
        } catch (Throwable $e) {
            // Fall through to final fallback
        }

        // Final fallback - microtime with process ID
        return sprintf('ERR-FB-%d-%d', (int)(microtime(true)*1000), getmypid());
    }
    
    protected static function displayDebugException(Throwable $e, string $category, string $errorId, array $suggestions = []): void {
        echo '
<div style="background:#fdd;padding:1em;margin:1em;border:1px solid red;">';
        echo '
<h3>Debug Exception</h3>';
        echo '
<p><strong>Error ID:</strong> ' . htmlspecialchars(
$errorId) . '</p>';
        echo '
<p><strong>Category:</strong> ' . htmlspecialchars($category) . '</p>';
        echo '
<p><strong>Type:</strong> ' . get_class(
$e) . '</p>';
        echo '
<p><strong>Message:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '
<p><strong>File:</strong> ' . htmlspecialchars(
$e->getFile()) . '</p>';
        echo '
<p><strong>Line:</strong> ' . $e->getLine() . '</p>';
        
        if (!empty($suggestions)) {
            echo '
<h4>Suggestions:</h4>';
            echo '
<ul>';
            foreach (
$suggestions as $suggestion) {
                echo '
<li>' . htmlspecialchars(
$suggestion) . '</li>';
            }
            echo '</ul>';
        }
        
        echo '
<h4>Stack Trace:</h4>';
        echo '
<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        echo '
</div>';
    }
    
    // Other methods remain the same...
    public static
 function handleShutdown(): void {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handleError($error['type'], $error['message'], $error['file'], $error['line']);
        }
    }

    protected static function log(string $message): void {
        if (self::$logToFile) {
            try {
                file_put_contents(self::$logFile, $message . PHP_EOL, FILE_APPEND);
                file_put_contents(\CMS_ROOT . '/logs/system.log', $message . PHP_EOL, FILE_APPEND);
            } catch (Throwable $e) {
                error_log("Failed to write to log file: " . $e->getMessage());
            }
        }
        if (self::$logToSyslog) {
            try {
                syslog(LOG_ERR, $message);
            } catch (Throwable $e) {
                error_log("Failed to write to syslog: " . $e->getMessage());
            }
        }
    }

    public static function setLogFile(string $path): void {
        self::$logFile = $path;
    }

    public static function setLogLevels(int $levels): void {
        self::$logLevels = $levels;
    }

    public static function setLoggingOptions(bool $file = true, bool $syslog = false): void {
        self::$logToFile = $file;
        self::$logToSyslog = $syslog;
    }

    protected static function getErrorType(int $errno): string {
        $types = [
            E_ERROR => 'Fatal Error',
            E_WARNING => 'Warning',
            E_PARSE => 'Parse Error',
            E_NOTICE => 'Notice',
            E_CORE_ERROR => 'Core Error',
            E_CORE_WARNING => 'Core Warning',
            E_COMPILE_ERROR => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR => 'User Error',
            E_USER_WARNING => 'User Warning',
            E_USER_NOTICE => 'User Notice',
            E_STRICT => 'Strict',
            E_RECOVERABLE_ERROR => 'Recoverable Error',
            E_DEPRECATED => 'Deprecated',
            E_USER_DEPRECATED => 'User Deprecated'
        ];
        
        return $types[$errno] ?? 'Unknown Error';
    }

    protected static function displayDebugError(int $errno, string $errstr, string $errfile, int $errline, string $category = 'GENERAL', string $errorId = ''): void {
        echo '
<div style="background:#fdd;padding:1em;margin:1em;border:1px solid red;">';
        echo '
<h3>Debug Error</h3>';
        if (!empty($errorId)) {
            echo '
<p><strong>Error ID:</strong> ' . htmlspecialchars(
$errorId) . '</p>';
        }
        if ($category !== 'GENERAL') {
            echo '
<p><strong>Category:</strong> ' . htmlspecialchars(
$category) . '</p>';
        }
        echo '
<p><strong>Type:</strong> ' . self::getErrorType($errno) . '</p>';
        echo '
<p><strong>Message:</strong> ' . htmlspecialchars(
$errstr) . '</p>';
        echo '
<p><strong>File:</strong> ' . htmlspecialchars($errfile) . '</p>';
        echo '
<p><strong>Line:</strong> ' .
 $errline . '</p>';
        echo '</div>';
    }
}
