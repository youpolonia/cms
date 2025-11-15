# Rate Limiting Configuration

## Global Configuration
Configured in `config/rate-limiter.php`:

```php
'global' => [
    'max_attempts' => 120,
    'decay_minutes' => 1,
],
```

## Per-User Limits
```php
'users' => [
    'max_attempts' => 30,
    'decay_minutes' => 1,
],
```

## API Endpoint Limits
```php
'api' => [
    'max_attempts' => 60,
    'decay_minutes' => 1,
],
```

## Authentication Endpoints
```php
'auth' => [
    'max_attempts' => 5,
    'decay_minutes' => 1,
],
```

## Error Responses
When rate limited:
```json
{
    "message": "Too Many Attempts",
    "retry_after": 60,
    "status": 429
}
```

## Customizing Limits
To customize for specific endpoints:
```php
RateLimiter::for('api.collaboration', function (Request $request) {
    return Limit::perMinute(100);
});