# Logging Standards and Best Practices

## LoggerFactory Usage

### Basic Instantiation
```php
// File logger with default config
$logger = LoggerFactory::getInstance();

// Custom logger instance
$logger = LoggerFactory::create('file', [
    'path' => '/path/to/custom.log'
]);
```

### Configuration Options
The logger supports these configuration options (set in `config/logger.php`):

| Option | Type | Default | Description |
|--------|------|---------|-------------|
| default_type | string | 'file' | Default logger type ('file' or 'database') |
| file.path | string | 'logs/app.log' | Path for file logger |
| file.max_size | string | '10MB' | Maximum log file size |
| database.table | string | 'system_logs' | Database table name |
| fallback.enabled | bool | true | Enable fallback mechanism |
| fallback.max_retries | int | 3 | Max retry attempts |

### Best Practices

1. **Always use LoggerFactory** - Never instantiate Logger classes directly
2. **Reuse instances** - Call `getInstance()` for singleton access
3. **Environment-specific configs** - Set different defaults per environment
4. **Proper log levels** - Use appropriate levels (DEBUG, INFO, WARNING, ERROR)
5. **Sensitive data** - Never log passwords or personal information

### Examples

**Static property usage:**
```php
class MyClass {
    private static $logger;
    
    public static function init() {
        self::$logger = LoggerFactory::create('file', [
            'path' => __DIR__ . '/../logs/myclass.log'
        ]);
    }
}
```

**Instance property usage:**
```php
class MyService {
    private $logger;
    
    public function __construct() {
        $this->logger = LoggerFactory::create('database');
    }
}
```

### Troubleshooting

**Common Issues:**
- Missing log files: Check directory permissions
- Database logging fails: Verify table exists
- Fallback not working: Check emergency.log permissions

**Debugging Tips:**
```php
// Check current logger config
$config = LoggerFactory::getConfig();