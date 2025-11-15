# CMS Error Logging System Documentation

## Overview

This document provides a comprehensive overview of the error logging system implemented in the CMS, with a particular focus on the worker monitoring components. The system consists of several interconnected components that work together to provide robust error logging and debugging capabilities.

## Core Components

### 1. Debug Worker Monitoring Classes

#### Base Class: `DebugWorkerMonitoring`
- **File Location**: `/var/www/html/cms/debug_worker_monitoring.php`
- **Purpose**: Provides base debugging functionality for worker monitoring
- **Key Methods**:
  - `init()`: Initializes debug logging
  - `logError($message, $exception = null)`: Logs error messages with optional exception details
  - `logApiRequest($endpoint, $params = [])`: Logs API requests
  - `logApiResponse($endpoint, $response, $status = 200)`: Logs API responses
  - `logAuth($type, $success, $reason = '')`: Logs authentication attempts
  - `logWorkerStatus($workers)`: Logs worker status updates
  - `getClientDebugScript()`: Returns JavaScript for client-side debugging

#### Enhanced Class: `DebugWorkerMonitoringPhase5`
- **File Location**: `/var/www/html/cms/debug_worker_monitoring_phase5.php`
- **Purpose**: Provides enhanced debugging for PHASE5-WORKFLOW-STEP4
- **Key Methods**:
  - `init()`: Initializes enhanced debug logging
  - `logJwtDetails($payload)`: Logs JWT token details to track expiration issues
  - `logResponseValidation($endpoint, $response, $isValid, $reason = '')`: Logs API response structure validation
  - `getClientDebugScript()`: Returns enhanced JavaScript for client-side debugging

### 2. API Endpoints for Client-Side Error Logging

#### General Error Logging Endpoint
- **File Location**: `/var/www/html/cms/api/debug/log-client-error.php`
- **Purpose**: Receives and logs client-side errors from the worker monitoring dashboard
- **Functionality**:
  - Accepts POST requests with JSON data
  - Validates and sanitizes error data
  - Logs errors to `/var/www/html/cms/logs/client_errors.log`

#### Enhanced Error Logging Endpoint
- **File Location**: `/var/www/html/cms/api/debug/log-client-error-phase5.php`
- **Purpose**: Specialized endpoint for PHASE5-WORKFLOW-STEP4 with enhanced logging capabilities
- **Functionality**:
  - Accepts POST requests with JSON data
  - Validates and sanitizes error data
  - Categorizes errors (authentication, structure, etc.)
  - Logs to multiple specialized log files:
    - `/var/www/html/cms/logs/phase5_client_errors.log`
    - `/var/www/html/cms/logs/phase5_auth_errors.log`
    - `/var/www/html/cms/logs/phase5_structure_errors.log`

### 3. Worker API Endpoints

Several worker API endpoints utilize the error logging system:

- **File Locations**:
  - `/var/www/html/cms/api/workers/status.php`
  - `/var/www/html/cms/api/workers/heartbeat-history.php`
  - `/var/www/html/cms/api/workers/alert-config.php`
  - `/var/www/html/cms/api/workers/refresh-token.php`

- **Integration**:
  - Include both debug monitoring files
  - Call `DebugWorkerMonitoringPhase5::logError()` for error logging

### 4. Client-Side Debugging Scripts

Both debug monitoring classes provide JavaScript code for client-side debugging:

#### Base Script (`DebugWorkerMonitoring::getClientDebugScript()`)
- **Purpose**: Basic client-side debugging for worker monitoring
- **Functionality**:
  - Intercepts fetch requests to worker API endpoints
  - Logs API requests and responses
  - Detects and logs JavaScript errors
  - Sends errors to the server via `/api/debug/log-client-error`

#### Enhanced Script (`DebugWorkerMonitoringPhase5::getClientDebugScript()`)
- **Purpose**: Enhanced client-side debugging for PHASE5-WORKFLOW-STEP4
- **Functionality**:
  - Intercepts fetch requests to worker API endpoints
  - Validates response structure
  - Detects authentication errors
  - Displays errors directly in the UI
  - Sends errors to the server via `/api/debug/log-client-error-phase5`

## Log Files

The system creates several log files:

- `/var/www/html/cms/logs/worker_monitoring_debug.log`: General debug logs
- `/var/www/html/cms/logs/worker_monitoring_phase5_debug.log`: Enhanced debug logs for PHASE5-WORKFLOW-STEP4
- `/var/www/html/cms/logs/client_errors.log`: Client-side errors
- `/var/www/html/cms/logs/phase5_client_errors.log`: Client-side errors for PHASE5-WORKFLOW-STEP4
- `/var/www/html/cms/logs/phase5_auth_errors.log`: Authentication-related errors
- `/var/www/html/cms/logs/phase5_structure_errors.log`: Response structure errors

## Method Implementation Details

### `DebugWorkerMonitoring::logError()`

```php
public static function logError($message, $exception = null) {
    $logMessage = 'ERROR: ' . $message;
    
    if ($exception) {
        $logMessage .= ' - ' . get_class($exception) . ': ' . $exception->getMessage();
        $logMessage .= ' in ' . $exception->getFile() . ' on line ' . $exception->getLine();
    }
    
    self::log($logMessage);
}
```

This method is defined in the `DebugWorkerMonitoring` class and is used to log error messages with optional exception details.

### `DebugWorkerMonitoringPhase5::logError()`

The `logError` method is not explicitly defined in the `DebugWorkerMonitoringPhase5` class. However, it is called in various worker API endpoints. This suggests that one of the following is happening:

1. The method is being inherited or borrowed from the `DebugWorkerMonitoring` class through PHP's method resolution when both files are included.
2. There might be a PHP feature or pattern being used that allows the `DebugWorkerMonitoringPhase5` class to use methods from the `DebugWorkerMonitoring` class without explicit inheritance or method definition.

## Usage Flow

1. **Server-Side Error Logging**:
   - Worker API endpoints include both debug monitoring files
   - When an error occurs, they call `DebugWorkerMonitoringPhase5::logError()`
   - The error is logged to the appropriate log file

2. **Client-Side Error Logging**:
   - The worker monitoring dashboard includes the client-side debugging script
   - The script intercepts fetch requests and detects errors
   - Errors are sent to the appropriate API endpoint
   - The API endpoint logs the error to the appropriate log file

## Conclusion

The CMS error logging system provides comprehensive error logging and debugging capabilities for the worker monitoring system. It consists of server-side and client-side components that work together to capture, categorize, and log errors. The system is particularly enhanced for PHASE5-WORKFLOW-STEP4, with specialized logging and debugging capabilities.