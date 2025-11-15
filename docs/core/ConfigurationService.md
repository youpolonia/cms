# ConfigurationService

Framework-free configuration management service for CMS.

## Features
- Singleton pattern implementation
- Runtime configuration management
- Simple key-value storage
- Type-safe getters/setters

## Usage

### Basic Example
```php
// Get configuration value
$dbHost = ConfigurationService::get('database.host');

// Set runtime configuration
ConfigurationService::set('app.debug', true);

// Check if config exists
if (ConfigurationService::has('security.key')) {
    // Do something
}
```

### Loading Configuration
1. Create `config/app.php` with your settings:
```php
return [
    'app' => [
        'name' => 'My CMS',
        'debug' => false
    ]
];
```

2. The service automatically loads this file on first use

## Best Practices
- Store sensitive data outside the config file (use environment variables)
- Group related settings under common keys
- Use dot notation for nested keys

## API Reference

### `get(string $key, mixed $default = null)`
Retrieves a configuration value

### `set(string $key, mixed $value)`
Sets a runtime configuration value

### `has(string $key)`
Checks if a configuration key exists

### `getInstance()`
Returns the singleton instance