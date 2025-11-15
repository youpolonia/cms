# API Path Standardization Rules

## Core Constants
- Use these predefined constants for path references:
  - `CMS_ROOT`: Root directory of the CMS
  - `CORE_DIR`: Core functionality (`/core`)
  - `INCLUDES_DIR`: Includes directory (`/includes`)
  - `PUBLIC_DIR`: Public directory (`/public`)
  - `API_DIR`: API directory (`/public/api`)

## Require Patterns

### For core functionality:
```php
require_once CORE_DIR . '/filename.php';
```

### For API-specific includes:
```php
require_once API_DIR . '/includes/filename.php';
```

### For cross-system paths:
```php
require_once CMS_ROOT . '/path/to/file.php';
```

## API Endpoint Structure
1. All API endpoints should be in `/public/api`
2. Group endpoints by functionality in subdirectories
3. Test endpoints go in `/public/api/test`
4. Shared API utilities go in `/public/api/includes`

## Examples

### Before (current):
```php
require_once __DIR__.'/../../../api/includes/api_error_handler.php';
```

### After (standardized):
```php
require_once API_DIR . '/includes/api_error_handler.php';
```

## Implementation Steps
1. Add API_DIR constant to core/constants.php
2. Update all API endpoints to use standardized require patterns
3. Move shared API utilities to /public/api/includes
4. Document any exceptions in this file