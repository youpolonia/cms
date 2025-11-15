# Logger Module

Centralized logging system for the CMS using factory pattern.

## LoggerFactory

The `LoggerFactory` class provides a centralized way to create and manage logger instances.

### Features
- Singleton pattern ensures single logger instance
- Automatic fallback to file logging if database logging fails
- Configurable logger types (file/database)
- Environment-aware defaults

### Usage

```php
// Basic usage with defaults
$logger = LoggerFactory::getInstance();
$logger->log("Message");

// Configure logger
LoggerFactory::configure([
    'type' => 'database', // or 'file'
    'db_config' => [ /* database config */ ],
    'file_path' => 'logs/app.log' 
]);

// Create new logger instance (non-singleton)
$logger = LoggerFactory::create('file', ['path' => 'custom.log']);
```

### Configuration Options

| Parameter | Type | Description | Default |
|-----------|------|-------------|---------|
| type | string | Logger type ('file' or 'database') | 'file' in dev, 'database' in prod |
| file_path | string | Path for file logger | 'logs/app.log' |
| db_config | array | Database connection config for database logger | [] |

### Fallback Behavior

If database logging fails:
1. System attempts to log failure to database
2. Falls back to file logger
3. Logs original message plus error details to file
4. Continues using file logger for subsequent messages

### Examples

**File Logger:**
```php
LoggerFactory::configure([
    'type' => 'file',
    'file_path' => 'logs/custom.log'
]);
```

**Database Logger:**
```php
LoggerFactory::configure([
    'type' => 'database',
    'db_config' => [
        'host' => 'localhost',
        'dbname' => 'cms',
        'user' => 'user',
        'password' => 'pass'
    ]
]);
```

## Testing

Run the test script:
```bash
php tests/LoggerTest.php
```

Tests verify:
- File logging
- Database logging
- Fallback behavior