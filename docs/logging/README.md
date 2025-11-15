# Logging System Documentation

## Overview
The CMS logging system provides flexible, reliable logging with automatic fallback mechanisms. It implements PSR-3-like functionality while maintaining framework independence.

## Core Components

### LoggerInterface
- Defines the core logging contract
- Single `log()` method with message, context and level parameters
- Implemented by all concrete loggers

### BaseLogger (Abstract)
- Provides common functionality:
  - Context serialization
  - Timestamp generation
  - Basic validation

### Concrete Loggers
1. **FileLogger**
   - Writes logs to filesystem
   - Automatic directory creation
   - Thread-safe file handling

2. **DatabaseLogger**  
   - Stores logs in database table
   - Automatic schema validation
   - Prepared statement usage

3. **EmergencyLogger**
   - **Guaranteed Delivery**: Will attempt all available outputs until success
   - **Output Options**:
     - File (with atomic write guarantees)
     - STDERR (when filesystem unavailable)
     - System log (when configured)
   - **Features**:
     - Process state capture (memory, stack trace)
     - Automatic log compression when disk space low
     - Fail-safe circular buffer (last 100 messages retained)
   - **Activation**:
     - Automatic on multiple failures
     - Manual via `LoggerFactory::forceEmergencyMode()`

### LoggerFactory
- **Enhanced Features**:
  - Dynamic logger selection based on system health
  - Configuration hot-reloading (watches config file changes)
  - Adaptive retry policies (backoff based on failure patterns)
  - Circuit breaker pattern to prevent cascading failures

- **New Methods**:
  - `getSystemStatus()` - Returns current logger health state
  - `overrideFallback()` - Temporarily force specific fallback
  - `getPerformanceMetrics()` - Returns success/failure statistics

- **Configuration Additions**:
  ```php
  'adaptive' => [
      'health_check_interval' => 30, // seconds
      'failure_threshold' => 5,      // failures before switch
      'recovery_test_interval' => 300 // test primary every 5min
  ]
  ```

## Usage

### Basic Usage
```php
$logger = LoggerFactory::getInstance();
$logger->log('System started', ['component' => 'init']);
```

### Configuration
Create `config/logger.php`:
```php
return [
    'type' => 'database', // or 'file'
    'file_path' => 'logs/app.log',
    'db_config' => [
        'table' => 'system_logs',
        // other PDO connection params
    ],
    'fallback' => [
        'emergency_path' => 'logs/emergency.log',
        'use_stderr' => true,
        'max_retries' => 3
    ]
];
```

Or configure programmatically:
```php
LoggerFactory::configure([
    'type' => 'file',
    'file_path' => 'custom.log'
]);
```

## Error Handling & Escalation
The system implements a robust error escalation protocol with multiple fallback layers:

1. **Primary Logger** (file or database)
   - 3 automatic retries with exponential backoff (100ms, 500ms, 2.5s)
   - Context preservation during retries
   - Success/failure metrics collection

2. **Secondary Fallback** (file if database fails)
   - Automatic switch when primary fails 3 times
   - Filesystem health checks before activation
   - Concurrent write protection

3. **Emergency Logger** (file or stderr)
   - Activates when all other layers fail
   - Minimal dependencies (no DB, no filesystem if using stderr)
   - Guaranteed delivery attempt
   - System status snapshot included

**Escalation Flow**:
1. Primary logger attempts (with retries)
2. On failure, secondary fallback activates
3. If secondary fails, emergency logger takes over
4. All failures are reported to system monitoring
5. Automatic recovery attempts every 5 minutes

## Best Practices
- Use context for structured data
- Choose appropriate log levels:
  - emergency, alert, critical
  - error, warning, notice  
  - info, debug
- Keep log messages concise but descriptive