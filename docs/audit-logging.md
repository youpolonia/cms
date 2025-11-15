# Audit Logging System Documentation

## Overview
The audit logging system provides comprehensive tracking of user actions and system events. It automatically captures key details including:
- User ID
- Action type
- Entity involved
- Timestamp
- IP address
- Tenant context (for multi-tenant installations)

## System Architecture
- **Core Class**: `App\Services\AuditLogger`
- **Storage**: SQLite database table `audit_logs`
- **Schema**:
```sql
CREATE TABLE audit_logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    tenant_id INTEGER,
    user_id INTEGER NOT NULL,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(255),
    entity_id INTEGER,
    ip_address VARCHAR(45),
    metadata TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
```

## Basic Usage

### Creating a Log Entry
```php
$logData = [
    'tenant_id' => 123, // Optional
    'user_id' => 456,   // Required
    'action' => 'login', // Required
    'entity_type' => 'user', // Optional
    'entity_id' => 789, // Optional
    'metadata' => ['ip' => '192.168.1.1'] // Optional
];

$logId = $auditLogger->createLog($logData);
```

### Required Fields
- `user_id`: Integer identifying the user
- `action`: String describing the action (max 255 chars)

## Key Integration Points
1. **Authentication System**: Automatically logs login attempts
2. **Content Management**: Tracks content creation/modification
3. **User Management**: Records user profile changes
4. **Multi-tenancy**: Supports tenant isolation for log retrieval

## Security and Privacy Considerations
- **IP Address Capture**: Automatically captured from `$_SERVER['REMOTE_ADDR']`
- **Data Minimization**: Only essential fields are required
- **Access Control**: Logs should only be accessible to admins
- **Retention Policy**: Recommend implementing log rotation/archiving

## Maintenance and Troubleshooting
### Common Issues
1. **Missing Required Fields**: Ensure `user_id` and `action` are provided
2. **Invalid Data Types**: All IDs must be integers, action must be string
3. **Tenant Isolation**: Verify tenant_id is set correctly in multi-tenant setups

### Verification Queries
```php
// Get single log entry
$logEntry = $db->query("SELECT * FROM audit_logs WHERE id = ?", [$logId]);

// Get logs for specific tenant  
$tenantLogs = $db->query("SELECT * FROM audit_logs WHERE tenant_id = ?", [$tenantId]);
```

## Performance Implications
- **Indexing**: Recommend adding indexes on:
  - `tenant_id` (for multi-tenant queries)
  - `user_id` (for user-specific lookups)
  - `created_at` (for time-based queries)
- **Volume**: High-traffic systems should consider:
  - Table partitioning by date
  - Asynchronous log writing
  - Regular archiving of old logs