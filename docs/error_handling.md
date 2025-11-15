# CMS Error Handling Standard

## Core Principles
1. **Never fail silently** - All errors must be logged
2. **Environment awareness** - Production vs development behavior
3. **Security first** - Never expose sensitive data
4. **Consistent formatting** - Standard error structure

## Implementation
### Base Handler ([`includes/ErrorHandler.php`](includes/ErrorHandler.php))
```php
ErrorHandler::init($fingerprint, $userId);
```

### Error Types
| Type       | Example                  | Log Level |
|------------|--------------------------|-----------|
| Critical   | Database failures        | ERROR     |
| Warning    | Deprecated API usage     | WARNING   |
| Info       | Cache misses             | INFO      |

## API Error Handling
```json
{
  "error": {
    "code": 404,
    "message": "Resource not found",
    "request_id": "abc123",
    "documentation": "/docs/errors/404"
  }
}
```

## Testing Requirements
1. Always restore handlers in `tearDown()`
2. Verify error responses match specs
3. Test production vs development modes

## Migration Guide
1. Replace all `@` operators with proper try/catch
2. Convert `error_log()` calls to `ErrorHandler::log()`
3. Update API endpoints to use `APIErrorHandler`