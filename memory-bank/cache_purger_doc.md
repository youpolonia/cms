# Cache Purger Toolkit Documentation

## Overview

The Cache Purger Toolkit provides a placeholder implementation for future cache directory clearing operations within the CMS. Currently implemented as a scaffold with logging and monitoring infrastructure, the toolkit is designed to support cache purging functionality while maintaining complete DEV_MODE security restrictions.

## Purpose

This toolkit serves as a development foundation for cache management operations, specifically:
- Cache directory purging and cleanup
- Cache invalidation operations  
- Cache storage monitoring
- Development testing of cache clearing infrastructure

## Navigation Links

The toolkit integrates with the admin navigation system providing the following developer endpoints:

- **Run Cache Purger** (`/admin/test-cache/run_cache_purger.php`) - Execute cache purger operations
- **Cache Purger Logs** (`/admin/test-cache/cache_purger_logs.php`) - View execution history and logging
- **Cache Purger Status** (`/admin/test-cache/cache_purger_status.php`) - Monitor implementation status and system readiness

## DEV_MODE Restriction

**CRITICAL SECURITY REQUIREMENT**: All Cache Purger toolkit endpoints are protected by DEV_MODE guards:

```php
if (!defined('DEV_MODE') || !DEV_MODE) {
    http_response_code(403);
    die('Access denied. DEV_MODE required.');
}
```

- **Development Access**: Only available when DEV_MODE is enabled
- **Production Safety**: Returns HTTP 403 Forbidden in production environments
- **No Override**: Cannot be bypassed - production installations are completely protected

## Logging

### Execution Tracking
All Cache Purger operations are logged to the central migrations.log file:

**Log Format**: `[TIMESTAMP] CachePurgerTask called (not implemented)`

**Example Entry**: 
```
[2025-09-12 14:30:25] CachePurgerTask called (not implemented)
```

### Log Viewing
- Execution history accessible via Cache Purger Logs endpoint
- Filtered display showing only CachePurgerTask entries
- Configurable limit parameter for log viewing
- Integration with existing log management infrastructure

## Usage

### Current Implementation
The Cache Purger toolkit is currently a **placeholder implementation**:

1. **CachePurgerTask Class**: Returns false and logs execution attempts
2. **Test Scaffold**: Provides testing infrastructure for future functionality
3. **Monitoring Ready**: Complete logging and status reporting infrastructure
4. **Future Preparation**: Structure prepared for actual cache purging implementation

### Development Testing
- Use Run Cache Purger endpoint to test infrastructure and DEV_MODE gating
- Monitor execution attempts via Cache Purger Logs
- Verify system readiness via Cache Purger Status
- Test logging integration and audit trail functionality

### Future Cache Purging Operations
When fully implemented, the toolkit will support:
- Selective cache directory clearing
- Cache invalidation by pattern or age
- Bulk cache cleanup operations
- Cache storage monitoring and reporting

## Security

### DEV_MODE Only Access
- **Production Protection**: Zero production exposure through DEV_MODE restriction
- **Safe Placeholder**: Current implementation performs no system operations
- **Audit Trail**: All execution attempts logged for development tracking
- **No Sensitive Data**: No credential exposure or sensitive information in responses
- **Future Security Framework**: Structure prepared for secure cache management implementation