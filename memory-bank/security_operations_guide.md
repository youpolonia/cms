# Phase 22 Security Operations Guide

## Tenant Monitoring System

### Key Components
1. **TenantMonitor Service**
   - Location: `services/security/TenantMonitor.php`
   - Responsibilities:
     - Real-time activity tracking
     - Anomaly detection
     - Alert generation

2. **Security Logs**
   - Location: `logs/tenant_security.log`
   - Format: JSON lines with timestamped entries
   - Retention: 30 days (configurable)

### Standard Procedures

#### Monitoring Tenant Activity
```php
// Track tenant action
TenantMonitor::trackActivity(
    $tenantId, 
    'content_update', 
    ['content_id' => 123, 'user_id' => 456]
);
```

#### Handling Security Alerts
1. Alerts are automatically generated when:
   - Activity exceeds threshold (3+ similar actions in 5 minutes)
   - Unusual patterns detected

2. Alert destinations:
   - Security log file
   - MCP Alert system (if available)
   - Admin dashboard notifications

#### Access Control Enforcement
- Uses TenantIsolation middleware
- Validates X-Tenant-ID header
- Prevents cross-tenant access

### Maintenance Procedures
1. **Log Rotation**
   - Daily rotation via cron job
   - Compress old logs after 7 days
   - Delete after 30 days

2. **Threshold Adjustment**
   - Modify `ANOMALY_THRESHOLD` constant
   - Default: 3 events/5 minutes

### Emergency Response
1. **Lockout Procedures**
   - Manual tenant lockout via admin panel
   - Automatic lockout after 5 alerts/hour

2. **Forensic Analysis**
   - Export security logs for investigation
   - Correlate with audit logs