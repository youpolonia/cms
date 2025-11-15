# Rate Limiting System

## Configuration
Rate limits are configured in `config/rate-limiter.php`. The format is:
```php
'feature' => [
    'subfeature' => 'MAX_REQUESTS,TIME_WINDOW_MINUTES',
    'tenant' => [
        'type' => 'MAX_REQUESTS,TIME_WINDOW_MINUTES'
    ]
]
```

## Usage
```php
// Basic usage
if (!RateLimiter::check('analytics.api', $userId)) {
    throw new RateLimitExceededException();
}

// Tenant-specific limits
if (!RateLimiter::check('analytics.tenant', $tenantId, $tenantType)) {
    throw new RateLimitExceededException();
}
```

## Environment Variables
- `ANALYTICS_API_LIMIT`: Global API limit (default: 120,1)
- `ANALYTICS_TENANT_DEFAULT_LIMIT`: Default tenant limit (default: 1000,1)
- `ANALYTICS_TENANT_PREMIUM_LIMIT`: Premium tenant limit (default: 5000,1)
- `ANALYTICS_TENANT_ENTERPRISE_LIMIT`: Enterprise tenant limit (default: 10000,1)
- `ANALYTICS_WEBHOOK_LIMIT`: Webhook limit (default: 500,1)

## Testing
The rate limiter includes comprehensive tests that verify:
- Basic rate limiting functionality
- Tenant-specific limits
- Invalid limit type handling
- Cache directory creation

To run the tests:
```php
require_once __DIR__ . '/../tests/RateLimiterTest.php';
RateLimiterTest::runTests();
```

Test files are automatically cleaned up after execution.