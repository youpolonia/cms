# Security Log Implementation Specification

## File Location & Access
- **Path:** `/var/www/html/cms/logs/security.log`
- **Access Restrictions:**
  - ✅ Outside web root (not in public directory)
  - ✅ Protected by `.htaccess` with "Deny from all"
  - ✅ Directory listing prevented
  - ✅ Direct URL access blocked

## Implementation Details
```php
// Example usage from SecurityLogger.php
SecurityLogger::log(
    'AUTH_FAILURE', 
    'Invalid login attempt',
    ['username' => 'admin', 'ip' => '192.168.1.1']
);
```

**Key Features:**
1. **Automatic Directory Creation:** Creates logs directory if missing (0755 permissions)
2. **Context Support:** Optional JSON-encoded context data
3. **Atomic Writes:** Uses `FILE_APPEND | LOCK_EX` for thread-safe writes
4. **Structured Format:** `[timestamp] [eventType] message [context]`

## Recommended Event Types
- `AUTH_SUCCESS` - Successful authentication
- `AUTH_FAILURE` - Failed login attempt  
- `PERMISSION_DENIED` - Authorization failure
- `SECURITY_ALERT` - Critical security events
- `SYSTEM_CHANGE` - Configuration modifications

## Session Security Configuration
- **Cookie Name:** CMSSESSID_ADMIN (admin-specific)
- **Security Settings:**
  - Secure flag: Enabled (HTTPS only)
  - HttpOnly: Enabled (no JavaScript access)
  - SameSite: Strict (CSRF protection)
  - Path restricted to /admin
  - 24 hour expiration
- **Implementation File:** `includes/config/session.php`
- **Log Event:** `SYSTEM_CHANGE` when modified

## Best Practices

### Logging Guidelines
1. Include relevant context:
   ```php
   SecurityLogger::log('AUTH_FAILURE', 'Brute force attempt detected', [
       'ip' => $_SERVER['REMOTE_ADDR'],
       'attempts' => 5,
       'username' => $username
   ]);
   ```
2. Use descriptive messages that explain the event
3. Keep sensitive data hashed or redacted

### File Management
- **Permissions:** 0600 (-rw-------) recommended
- **Rotation:** Implement log rotation for large files
- **Retention:** 30-90 days recommended for security logs

### Monitoring
1. Set up alerts for critical events:
   - Multiple `AUTH_FAILURE` from same IP
   - `SECURITY_ALERT` events
   - Unexpected `SYSTEM_CHANGE` events
2. Regular audit of log file permissions

## Example Log Entries
```
[2025-08-07T21:15:00+00:00] [AUTH_SUCCESS] User admin logged in {"ip":"192.168.1.100"}
[2025-08-07T21:15:05+00:00] [PERMISSION_DENIED] Unauthorized access attempt {"user":"guest","resource":"/admin"}
[2025-08-07T21:15:10+00:00] [SECURITY_ALERT] XSS attempt detected {"input":"<script>alert(1)</script>"}
```

## Verification Checklist
1. [ ] Confirm .htaccess protection exists
2. [ ] Test web access prevention
3. [ ] Verify log directory permissions (0755)
4. [ ] Check log file permissions after creation (0600)
5. [ ] Validate log entry format