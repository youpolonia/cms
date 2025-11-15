# Error Handling Chain Documentation

## Overview

This document provides a comprehensive analysis of the CMS error handling chain, including identified issues and proposed solutions. It serves as both a diagnostic report and a guide for implementing robust error handling throughout the system.

## Error Propagation Path

The error handling chain follows this sequence:

1. Error/exception occurs
2. PHP calls the appropriate handler:
   - `handleError()` for PHP errors
   - `handleException()` for exceptions
   - `handleShutdown()` for fatal errors during script termination
3. Handler generates an error ID via `generateErrorId()`
4. Error details are logged to file(s) (debug.log and system.log)
5. Error is displayed based on debug mode setting

## Identified Issues

### 1. Multiple Registration Points

The ErrorHandler is registered in multiple places:

```php
// bootstrap.php (root)
ErrorHandler::register(true);

// public/index.php
ErrorHandler::register(defined('DEV_MODE') && DEV_MODE === true);

// includes/bootstrap.php
\ErrorHandler::register($debugMode);

// Application::initErrorHandler()
// Re-registers handlers with a new ErrorHandler instance
```

This creates several potential issues:
- Handlers being overwritten by subsequent registrations
- Inconsistent debug mode settings across registrations
- Multiple ErrorHandler instances with different configurations

### 2. UNKNOWN Error ID Sources

The `generateErrorId()` method has three fallback mechanisms:
1. Primary: `uniqid('ERR-', true)`
2. Secondary: `random_bytes(8)`
3. Final fallback: `microtime() with getmypid()`

However, "UNKNOWN" doesn't appear in the code. Potential sources include:

1. **Direct Error ID Generation**: In `public/index.php`, `ErrorHandler::generateErrorId()` is called directly in the catch block, bypassing the normal exception handler flow
2. **Error ID Parsing**: The `emergency_error_test.php` looks for "UNKNOWN" in logs, suggesting it's an expected value
3. **Early Initialization Failures**: Errors occurring before ErrorHandler registration
4. **Fallback Mechanism Failures**: If all three ID generation methods fail

### 3. Early Initialization Error Handling

Critical vulnerabilities exist for errors occurring before ErrorHandler registration:
- Errors in defining CMS_ROOT
- Errors in loading ErrorHandler.php itself
- Errors in the ErrorHandler::register() method
- Autoloader failures before error handling is established

### 4. Inconsistent Error ID Generation

Error IDs are generated in multiple ways:
- Through the normal error/exception handlers
- Directly in catch blocks (e.g., public/index.php)
- Potentially through custom error handling code

## Proposed Solutions

### 1. Singleton Pattern Implementation

Implement a true singleton pattern for ErrorHandler to prevent multiple instances:

```php
class ErrorHandler {
    private static $instance = null;
    
    private function __construct() {
        // Private constructor
    }
    
    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
```

### 2. Pre-Registration Error Buffer

Add a buffer for errors that occur before registration:

```php
protected static $preRegistrationErrors = [];
protected static $isRegistered = false;

public static function bufferPreRegistrationError(string $type, string $message, string $file, int $line): void {
    self::$preRegistrationErrors[] = [
        'type' => $type,
        'message' => $message,
        'file' => $file,
        'line' => $line,
        'time' => date('Y-m-d H:i:s')
    ];
}

protected static function processPreRegistrationErrors(): void {
    if (empty(self::$preRegistrationErrors)) {
        return;
    }
    
    foreach (self::$preRegistrationErrors as $error) {
        $message = sprintf(
            "[%s] [%s] Pre-registration %s: %s in %s on line %d",
            $error['time'],
            self::generateErrorId(),
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        self::log($message);
    }
    
    // Clear buffer
    self::$preRegistrationErrors = [];
}
```

### 3. Explicit UNKNOWN Error ID Handling

Add explicit handling for cases where error ID generation fails:

```php
const UNKNOWN_ERROR_ID = 'ERR-UNKNOWN';

public static function generateErrorId(): string {
    // Primary method - uniqid with entropy
    try {
        $id = uniqid('ERR-', true);
        if (preg_match('/^ERR-[a-zA-Z0-9]{13,23}$/', $id)) {
            return $id;
        }
    } catch (Throwable $e) {
        // Fall through to secondary method
    }
    
    // Secondary method - random_bytes
    try {
        $bytes = random_bytes(8);
        return 'ERR-' . bin2hex($bytes);
    } catch (Throwable $e) {
        // Fall through to final fallback
    }
    
    // Final fallback - microtime with process ID
    try {
        return sprintf('ERR-FB-%d-%d', (int)(microtime(true)*1000), getmypid());
    } catch (Throwable $e) {
        // All methods failed, return UNKNOWN
        return self::UNKNOWN_ERROR_ID;
    }
}
```

### 4. Standardized Registration

Standardize ErrorHandler registration to a single point early in the bootstrap process:

```php
// In bootstrap.php (root)
if (!defined('CMS_ROOT')) {
    define('CMS_ROOT', __DIR__);
}

require_once __DIR__ . '/includes/ErrorHandler.php';
ErrorHandler::register($debugMode);

// In other files, check if already registered
if (!ErrorHandler::isRegistered()) {
    ErrorHandler::register($debugMode);
}
```

## Testing

A comprehensive test case has been created to verify the error handling chain:

1. `debug_error_chain_test.php` - CLI test script
2. `public/error_chain_test.php` - Web interface for testing

These tests verify:
- Pre-registration error handling
- ErrorHandler registration
- Error ID generation
- Error propagation
- Multiple registration handling
- Early initialization errors
- Direct error ID generation

## Implementation

The proposed improvements have been implemented in `includes/ErrorHandler.patch.php`. This file contains a complete rewrite of the ErrorHandler class with all the recommended improvements.

To implement these changes:
1. Review the patch file
2. Apply the changes to the existing ErrorHandler.php
3. Update any code that directly instantiates ErrorHandler to use the singleton pattern
4. Standardize registration points to prevent multiple registrations
5. Run the test scripts to verify the improvements

## Best Practices

1. **Always check if ErrorHandler is registered** before using it:
   ```php
   if (ErrorHandler::isRegistered()) {
       // Use ErrorHandler
   } else {
       // Handle errors manually or register ErrorHandler
   }
   ```

2. **Use the singleton instance** rather than static methods when possible:
   ```php
   $errorHandler = ErrorHandler::getInstance();
   ```

3. **Standardize error ID generation** by using ErrorHandler's methods:
   ```php
   $errorId = ErrorHandler::generateErrorId();
   ```

4. **Buffer pre-registration errors** when necessary:
   ```php
   try {
       // Code that might fail before ErrorHandler is registered
   } catch (Throwable $e) {
       ErrorHandler::bufferPreRegistrationError('Exception', $e->getMessage(), $e->getFile(), $e->getLine());
   }
   ```

5. **Check for UNKNOWN error IDs** in logs and address their sources:
   ```php
   if ($errorId === ErrorHandler::UNKNOWN_ERROR_ID) {
       // Take special action for unknown error IDs
   }