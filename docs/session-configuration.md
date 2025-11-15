# Session Configuration

## Standardization Between Environments

As of 2025-04-28, we've standardized session configuration between debug and production environments while maintaining debug-specific flexibility through environment variables.

### Key Changes Made:

1. **Session Driver**  
   - Debug now uses database driver by default (configurable via SESSION_DRIVER env)
   - Previously used file driver in debug environment

2. **Session Lifetime**  
   - Standardized to 120 minutes (2 hours) for both environments  
   - Previously 360 minutes in debug environment

3. **Cookie Naming**  
   - Uses dynamic naming based on APP_NAME env
   - Debug sessions use 'cms_session_debug' naming

4. **Security Settings**  
   - Added missing security-related options to debug config:
     - Session encryption (SESSION_ENCRYPT)
     - Partitioned cookies (SESSION_PARTITIONED_COOKIE)
     - Same-site cookie policy
     - HTTP-only cookies

### Environment Variables

Debug environment can still override defaults using these env vars:

```
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_COOKIE=your_app_name_session
SESSION_ENCRYPT=false
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_PARTITIONED_COOKIE=false
```

### Verification

To verify your session configuration:

1. Check active session settings:
```php
php artisan tinker
>>> config('session')
```

2. View session table records:
```php
php artisan session:table
php artisan migrate