# Extension Runtime Enforcement Integration Guide

This guide explains how to integrate the extension runtime enforcement system into your CMS.

## Files Created

1. `/core/extensions_state.php` - Helper functions for extension state management
2. `/core/extension_loader.php` - Extension loader with runtime enforcement
3. `/admin/extensions/toggle.php` - CSRF-protected toggle endpoint
4. `/admin/extensions/state.json` - Extension state persistence file

## Integration Steps

### 1. Add Extension Loading to Bootstrap

Add this line to your main bootstrap file (e.g., `bootstrap.php`, `config.php`, or initialization script):

```php
// Load extensions with runtime enforcement
require_once __DIR__ . '/core/extension_loader.php';
```

### 2. Alternative Manual Loading

If you prefer manual control, prevent auto-loading and call explicitly:

```php
// Prevent automatic loading
define('PREVENT_EXTENSION_AUTOLOAD', true);
require_once __DIR__ . '/core/extension_loader.php';

// Load extensions at the right time in your init process
load_extensions();
```

### 3. Extension Structure

Extensions should be placed in `/extensions/` directory with this structure:

```
extensions/
├── hello-world/
│   ├── extension.json     # Manifest with slug, name, version
│   └── bootstrap.php      # Extension initialization code
└── another-extension/
    ├── extension.json
    └── bootstrap.php
```

### 4. Runtime Enforcement

The system will:
- Check each extension's enabled/disabled state from `state.json`
- Skip loading disabled extensions completely
- Log all extension loading events to `/logs/extensions.log`
- Default to enabled if no state is defined

### 5. State Management

Extensions can be enabled/disabled via:
- Admin interface at `/admin/extensions/`
- Toggle endpoint: `POST /admin/extensions/toggle.php` with CSRF protection
- Direct state.json manipulation

## Example Extension

Create `/extensions/hello-world/extension.json`:
```json
{"slug":"hello-world","name":"Hello World","version":"1.0.0"}
```

Create `/extensions/hello-world/bootstrap.php`:
```php
<?php
// Hello World Extension Bootstrap
// This file is only loaded if the extension is enabled

add_action('init', function() {
    error_log('Hello World extension loaded!');
});

// Register hooks, filters, or other initialization code here
```

## Security Features

- CSRF protection on toggle endpoints
- Audit logging of all extension state changes
- Atomic state file updates (tmp -> rename pattern)
- Input validation on extension slugs
- Proper error handling and logging

## Verification

1. Check extension loading: Look at `/logs/extensions.log`
2. Test disable: Use admin interface to disable an extension
3. Verify enforcement: Disabled extensions should not load on next request
4. Monitor audit trail: All state changes are logged with IP addresses

The system is now ready to provide runtime enforcement for extension enable/disable functionality.