# Error Handling System Documentation

## 1. Implementation Overview

The CMS implements a global error handling system via PHP's `set_error_handler()` function. Key features:

- Custom error level mapping:
  - `E_USER_NOTICE` → `LogLevel::INFO`
  - `E_USER_WARNING` → `LogLevel::WARNING` 
  - `E_USER_ERROR` → `LogLevel::ERROR`

- Environment-aware behavior:
  - **Production mode**:
    - Logs detailed errors to `/var/log/cms_errors.log`
    - Displays generic "System Error" to users
  - **Development mode**:
    - Displays full error details with stack traces
    - Highlights relevant code sections

- Initialized in `index.php` before any other code
- Mode determined by `CMS_ENV` environment variable

## 2. Configuration Options

Configured via `/config/error_logging.php`:

```php
return [
    // Maximum log file size in MB
    'max_log_size' => 10,
    
    // Number of rotated log files to keep
    'log_rotation' => 5,
    
    // Email notifications for critical errors
    'email_notifications' => true,
    
    // Log format components
    'format' => [
        'timestamp',
        'level',
        'message',
        'file:line',
        'request_uri',
        'user_id' // If authenticated
    ]
];
```

## 3. Usage Examples

### Triggering Errors:
```php
// User notice (info level)
trigger_error("Cache cleared successfully", E_USER_NOTICE);

// User warning 
trigger_error("Deprecated function called", E_USER_WARNING);

// User error (will halt execution)
trigger_error("Invalid API key", E_USER_ERROR);
```

### Custom Error Handler:
```php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Custom error handling logic
    if ($errno === E_USER_ERROR) {
        http_response_code(500);
        die(json_encode(['error' => $errstr]));
    }
});
```

## 4. Troubleshooting Tips

- **Error logs not appearing**:
  - Verify `/var/log/cms_errors.log` exists and is writable
  - Check `error_logging.php` configuration
  - Confirm `CMS_ENV` is set correctly

- **Development mode not showing errors**:
  - Ensure `display_errors` is On in php.ini
  - Verify `error_reporting` includes E_ALL

- **Common issues**:
  - Path resolution errors - use absolute paths
  - Permission errors - check file ownership
  - Memory limits - monitor with `memory_get_usage()`

## 5. Integration Requirements

### Required Files:
- `core/ErrorHandler.php` - Main error handling logic
- `config/error_logging.php` - Configuration
- `includes/bootstrap.php` - Early initialization
- `public/index.php` - Entry point setup
- `admin/login.php` - Special auth error handling

### API Integration:
API endpoints must:
- Return JSON-formatted errors
- Maintain consistent error format:
```json
{
    "error": {
        "code": "ERR_INVALID_INPUT",
        "message": "Invalid email format",
        "details": {
            "field": "email",
            "value": "user@example"
        }
    }
}
```

### Log Format:
All errors follow this format:
```
[YYYY-MM-DD HH:MM:SS] [LEVEL] Message in file.php:123
Request: /api/user/create
User: 42 (if authenticated)

# Logging System Improvements (2025-06-22)

## 1. Access Control Changes

### ErrorHandler Security
- Added SHA-256 fingerprint validation based on client headers
- Implemented separate security log file
- Validation occurs before any error processing
- New init() parameter for optional fingerprint
- Automatic fingerprint generation
- Security event logging

### EmergencyLogger Access
- Added IP whitelist for emergency logging
- Authentication required for log retrieval
- IP address tracking in all logs
- Preserved emergency logging for whitelisted IPs

### Admin AI Access
- Added .htaccess with basic auth and security headers
- Created index.php auth gateway with:
  - Admin permission checks
  - Secure logging for access attempts
  - Session validation
- IP logging for unauthorized attempts
- Restricted to authorized admins only

## 2. Error Handling Standardization

### Base ErrorHandler
- Standardized try/catch patterns
- Severity-based filtering (DEBUG, INFO, WARNING, ERROR, CRITICAL)
- Log rotation support
- Consistent error response format
- Alerting for critical errors

### AIErrorHandler Extensions
- AI-specific metrics (token usage, response times)
- Admin-side error handling
- Separate AI logging
- Standardized error responses across API endpoints

## 3. AI Logging Enhancements

### Operation Tracking
- Success/failure state tracking
- Input/output sanitization
- Performance metrics (duration)
- Standardized logging format
- Separate success/error logs

### Security Features
- Data sanitization for sensitive information
- Limited output size in logs
- No raw error traces in production
- All AI endpoints use sanitized input data

## 4. Security Considerations

### General Security
- Fingerprint validation for all error access
- Separate security log files
- Restricted access to error logs
- IP-based access controls

### AI-Specific Security
- No sensitive data in logs
- Limited error details in production
- Secure logging of report generation attempts
- File access checks for telemetry logs

### Implementation Notes
- All changes implemented in:
  - includes/ErrorHandler.php
  - auth/Services/EmergencyLogger.php  
  - includes/AI/AIErrorHandler.php
  - admin/ai/.htaccess
  - admin/ai/index.php
## Cache System Usage

To use the new caching system:

```php
// Get cached data (will regenerate if expired)
$settings = CacheManager::get('cache/settings.cache.php', function() {
    // Return fresh data here
    return ['setting1' => 'value1', 'setting2' => 'value2'];
});

// For AI cache
$aiData = CacheManager::get('cache/ai.cache.php', function() {
    // Return fresh AI data here
    return ['model' => 'gpt-4', 'last_updated' => time()];
});
```

Cache files:
- Automatically regenerated after 10 minutes
- Stored as PHP arrays for performance
- Located in cache/ directory