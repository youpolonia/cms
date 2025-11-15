<?php
/**
 * Debug Worker Monitoring
 * 
 * This file provides diagnostic logging for the worker monitoring system
 * to help identify issues with real-time updates and authentication.
 * 
 * @package CMS
 * @subpackage Debug
 */

// Prevent direct access
defined('CMS_ROOT') or die('No direct script access allowed');

class DebugWorkerMonitoring {
    private static $logFile = __DIR__ . '/logs/worker_monitoring_debug.log';
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize debug logging
     */
    public static function init() {
        // Create log directory if it doesn't exist
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        self::log('Debug Worker Monitoring initialized');
        
        // Register shutdown function to log any fatal errors
        register_shutdown_function(function() {
            $error = error_get_last();
            if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                self::log('FATAL ERROR: ' . $error['message'] . ' in ' . $error['file'] . ' on line ' . $error['line']);
            }
        });
    }
    
    /**
     * Log API request
     * 
     * @param string $endpoint API endpoint
     * @param array $params Request parameters
     */
    public static function logApiRequest($endpoint, $params = []) {
        self::log('API Request: ' . $endpoint . ' - Params: ' . json_encode($params));
    }
    
    /**
     * Log a message directly (public wrapper for the private log method)
     *
     * @param string $message Message to log
     */
    public static function logMessage($message) {
        self::log($message);
    }
    
    /**
     * Log API response
     * 
     * @param string $endpoint API endpoint
     * @param mixed $response Response data
     * @param int $status HTTP status code
     */
    public static function logApiResponse($endpoint, $response, $status = 200) {
        // Truncate response if too large
        $responseData = is_string($response) ? $response : json_encode($response);
        if (strlen($responseData) > 1000) {
            $responseData = substr($responseData, 0, 1000) . '... [truncated]';
        }
        
        self::log('API Response: ' . $endpoint . ' - Status: ' . $status . ' - Data: ' . $responseData);
    }
    
    /**
     * Log authentication attempt
     * 
     * @param string $type Authentication type (session, jwt)
     * @param bool $success Whether authentication was successful
     * @param string $reason Reason for failure if unsuccessful
     */
    public static function logAuth($type, $success, $reason = '') {
        $status = $success ? 'SUCCESS' : 'FAILURE';
        self::log('Authentication (' . $type . '): ' . $status . ($reason ? ' - Reason: ' . $reason : ''));
    }
    
    /**
     * Log worker status update
     * 
     * @param array $workers Worker status data
     */
    public static function logWorkerStatus($workers) {
        $count = is_array($workers) ? count($workers) : 0;
        self::log('Worker Status Update: ' . $count . ' workers');
        
        if (is_array($workers)) {
            foreach ($workers as $worker) {
                $status = isset($worker['status']) ? $worker['status'] : 'unknown';
                $lastHeartbeat = isset($worker['last_heartbeat']) ? $worker['last_heartbeat'] : 'never';
                self::log('  Worker: ' . ($worker['id'] ?? 'unknown') . ' - Status: ' . $status . ' - Last Heartbeat: ' . $lastHeartbeat);
            }
        }
    }
    
    /**
     * Log error
     * 
     * @param string $message Error message
     * @param \Exception $exception Exception object if available
     */
    public static function logError($message, $exception = null) {
        $logMessage = 'ERROR: ' . $message;
        
        if ($exception) {
            $logMessage .= ' - ' . get_class($exception) . ': ' . $exception->getMessage();
            $logMessage .= ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
        }
        
        self::log($logMessage);
    }
    
    /**
     * Log message to file
     * 
     * @param string $message Message to log
     */
    private static function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = '[' . $timestamp . '] ' . $message . PHP_EOL;
        
        file_put_contents(self::$logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Get JavaScript for client-side debugging
     * 
     * @return string JavaScript code
     */
    public static function getClientDebugScript() {
        return <<<'JS'
<script>
// Debug logging for worker monitoring
const WorkerMonitoringDebug = {
    // Log levels
    LEVELS: {
        INFO: 'INFO',
        WARNING: 'WARNING',
        ERROR: 'ERROR'
    },
    
    // Initialize debug logging
    init: function() {
        console.log('Worker Monitoring Debug initialized');
        
        // Override fetch for API requests
        const originalFetch = window.fetch;
        window.fetch = function(url, options) {
            // Only intercept worker API requests
            if (typeof url === 'string' && url.includes('/api/workers/')) {
                WorkerMonitoringDebug.logApiRequest(url, options);
                
                return originalFetch(url, options)
                    .then(response => {
                        // Clone the response so we can read the body
                        const clonedResponse = response.clone();
                        
                        // Process the response
                        clonedResponse.text().then(text => {
                            try {
                                const data = JSON.parse(text);
                                WorkerMonitoringDebug.logApiResponse(url, data, response.status);
                            } catch (e) {
                                WorkerMonitoringDebug.logApiResponse(url, text, response.status);
                            }
                        });
                        
                        return response;
                    })
                    .catch(error => {
                        WorkerMonitoringDebug.log(
                            `API Request failed: ${url} - ${error.message}`, 
                            WorkerMonitoringDebug.LEVELS.ERROR
                        );
                        
                        // Log to server
                        WorkerMonitoringDebug.sendErrorToServer(url, error.message);
                        
                        throw error;
                    });
            }
            
            return originalFetch(url, options);
        };
        
        // Add error event listener
        window.addEventListener('error', function(event) {
            WorkerMonitoringDebug.log(
                `JavaScript Error: ${event.message} at ${event.filename}:${event.lineno}`,
                WorkerMonitoringDebug.LEVELS.ERROR
            );
            
            // Log to server
            WorkerMonitoringDebug.sendErrorToServer(
                event.filename, 
                `${event.message} at line ${event.lineno}`
            );
        });
    },
    
    // Log API request
    logApiRequest: function(url, options) {
        this.log(`API Request: ${url}`, this.LEVELS.INFO);
    },
    
    // Log API response
    logApiResponse: function(url, data, status) {
        const level = status >= 400 ? this.LEVELS.ERROR : this.LEVELS.INFO;
        this.log(`API Response: ${url} - Status: ${status}`, level);
        
        // Check for specific issues
        if (data && data.error) {
            this.log(`API Error: ${data.error}`, this.LEVELS.ERROR);
            
            // Check for authentication errors
            if (data.error.includes('Access denied') || 
                data.error.includes('Unauthorized') || 
                data.error.includes('Forbidden')) {
                this.log('Authentication error detected', this.LEVELS.ERROR);
                
                // Display error in UI
                this.showErrorInUI('Authentication error: ' + data.error);
            }
        }
    },
    
    // Log message
    log: function(message, level = this.LEVELS.INFO) {
        const timestamp = new Date().toISOString();
        const formattedMessage = `[${timestamp}] [${level}] ${message}`;
        
        switch (level) {
            case this.LEVELS.ERROR:
                console.error(formattedMessage);
                break;
            case this.LEVELS.WARNING:
                console.warn(formattedMessage);
                break;
            default:
                console.log(formattedMessage);
        }
    },
    
    // Send error to server
    sendErrorToServer: function(source, message) {
        const data = {
            source: source,
            message: message,
            url: window.location.href,
            timestamp: new Date().toISOString()
        };
        
        // Use navigator.sendBeacon to ensure the request is sent even if page is unloading
        if (navigator.sendBeacon) {
            navigator.sendBeacon('/api/debug/log-client-error', JSON.stringify(data));
        } else {
            // Fallback to fetch
            fetch('/api/debug/log-client-error', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data),
                keepalive: true
            }).catch(e => console.error('Failed to send error to server:', e));
        }
    },
    
    // Show error in UI
    showErrorInUI: function(message) {
        // Create error element if it doesn't exist
        let errorContainer = document.getElementById('worker-monitoring-debug-errors');
        if (!errorContainer) {
            errorContainer = document.createElement('div');
            errorContainer.id = 'worker-monitoring-debug-errors';
            errorContainer.className = 'alert alert-danger mt-3';
            
            // Insert after worker-status-container
            const statusContainer = document.getElementById('worker-status-container');
            if (statusContainer && statusContainer.parentNode) {
                statusContainer.parentNode.insertBefore(errorContainer, statusContainer.nextSibling);
            } else {
                // Fallback to body
                document.body.appendChild(errorContainer);
            }
        }
        
        // Add error message
        const errorElement = document.createElement('p');
        errorElement.textContent = message;
        errorContainer.appendChild(errorElement);
    }
};

// Initialize debug logging when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    WorkerMonitoringDebug.init();
});
</script>
JS;
    }
}

// Initialize debug logging
DebugWorkerMonitoring::init();
