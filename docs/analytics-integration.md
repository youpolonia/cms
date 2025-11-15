# Analytics Integration Documentation

## Overview
The system implements a multi-layer analytics logging system with fallback mechanisms to ensure reliable tracking of tenant access patterns.

## Logging Architecture
1. **Primary Logging**: Database storage via `AnalyticsRepository`
2. **Fallback**: File-based logging when database fails
3. **Emergency**: Error logging when all else fails

## Data Collected
Each log entry includes:
- Tenant ID
- Timestamp
- Endpoint accessed
- HTTP method
- User agent (if available)
- IP address (if available)

## Implementation Details

### Database Logging
```php
AnalyticsRepository::logAccess([
    'tenant_id' => $tenantId,
    'timestamp' => time(),
    'endpoint' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null
]);
```

### File Fallback
When database logging fails, the system writes to:
```
storage/logs/analytics_fallback.log
```
Format: JSON objects separated by newlines

### Error Handling
If both database and file logging fail, the error is written to the system error log.

## Monitoring
Check these locations for analytics data:
1. Database: `analytics_access` table
2. Filesystem: `storage/logs/analytics_fallback.log`
3. System logs: For critical failures

## Retention Policy
- Database logs: 30 days
- File logs: 7 days (rotated daily)
- Error logs: System default