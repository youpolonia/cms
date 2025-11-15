# Health Management Toolkit Documentation

## Overview

The Health Management toolkit provides comprehensive system health monitoring capabilities for the CMS. The core component is `HealthCheckTask`, which performs real system health checks including directory existence, writability verification, and disk space monitoring. This is the first fully functional toolkit with actual implementation beyond placeholder behavior.

### HealthCheckTask Behavior

- **Purpose**: System health monitoring including directory checks, permissions, and disk space
- **Current Status**: Fully implemented with real health checks and detailed reporting
- **Normal Operation**: Performs comprehensive system checks and logs detailed results
- **Logging**: All executions logged to `logs/migrations.log` with detailed status and metrics

## Admin Navigation Links

The Health Management section in the admin navigation (DEV_MODE only) provides comprehensive monitoring functions:

### 1. Run Health Check
- **Endpoint**: `/admin/test-health/run_health_check.php`
- **Function**: Executes HealthCheckTask with real system health checks
- **Output**: JSON response with execution status (true for all checks passed, false otherwise)
- **Use Case**: On-demand system health verification and monitoring

### 2. Health Check Logs
- **Endpoint**: `/admin/test-health/health_check_logs.php`
- **Function**: Displays recent log entries from health check executions
- **Output**: Plain text log viewer with timestamps and detailed status metrics
- **Use Case**: Historical health check tracking and troubleshooting system issues

### 3. Health Check Status
- **Endpoint**: `/admin/test-health/health_check_status.php`
- **Function**: Real-time system health status without logging
- **Output**: Detailed JSON with per-directory status, disk space metrics, and overall health
- **Use Case**: Live system monitoring and detailed health assessment

## Security and Access Control

### DEV_MODE Gating
All health management endpoints are strictly gated by `DEV_MODE`:
- Immediate HTTP 403 response if `DEV_MODE` is not enabled
- No functionality exposed in production environments
- Admin navigation links only render when `DEV_MODE === true`

### System Access
- **Safe File Operations**: Only checks directory existence and permissions, no modifications
- **Read-Only Assessment**: Health checks perform no system modifications
- **Controlled Scope**: Limited to predefined directory list (logs, temp, sessions, backups, search_index)
- **No Credential Access**: Does not access or expose database credentials or sensitive data

## Logging Behavior

Health check operations are logged to `logs/migrations.log`:
- **Success Format**: `[TIMESTAMP] HealthCheckTask executed ok (dirs=5/5, writable=5/5, free=1/1)`
- **Failure Format**: `[TIMESTAMP] HealthCheckTask executed failed (dirs=4/5, writable=3/5, free=0/1)`
- **Append Mode**: New entries added without overwriting existing logs
- **Content**: Includes execution timestamp, overall status, and detailed metrics
- **Integration**: Uses same logging infrastructure as other scheduled tasks

## Usage Scenarios

### 1. System Environment Verification
- Verify all required directories exist and are properly configured
- Check directory permissions for write access
- Monitor available disk space for system operations
- Validate system readiness for production deployment

### 2. Ongoing System Monitoring
- Regular health checks to detect configuration drift
- Monitor disk space usage and availability
- Track directory permission changes
- Identify potential system issues before they impact operations

### 3. Troubleshooting and Diagnostics
- Diagnose system configuration problems
- Verify directory structure after deployments
- Check system health after configuration changes
- Generate detailed status reports for system administrators

## Technical Implementation

### Health Check Architecture
- **Class Structure**: HealthCheckTask with static run() method following established task pattern
- **Return Value**: Boolean indicating overall system health (all checks must pass)
- **Error Handling**: Graceful handling of file system operations with detailed reporting
- **Comprehensive Checks**: Directory existence, writability, and disk space verification

### System Checks Performed
- **Directory Existence**: Verifies presence of logs, temp, sessions, backups, search_index directories
- **Writability Assessment**: Confirms write permissions on all required directories
- **Disk Space Monitoring**: Ensures minimum 100MB free space available
- **Overall Status**: All checks must pass for overall success

### Status Reporting
- **Compact Summary**: Numerical ratios for quick assessment (dirs=5/5, writable=5/5, free=1/1)
- **Detailed JSON**: Per-directory status with existence and writability flags
- **Real-time Metrics**: Live disk space reporting in bytes
- **Historical Tracking**: Complete log history of all health check executions

### Performance Considerations
- **Lightweight Operations**: Minimal system impact with read-only file operations
- **Efficient Checks**: Fast directory and permission verification
- **Resource Monitoring**: Built-in disk space assessment
- **Safe Operations**: No system modifications or resource consumption

## Integration Notes

The Health Management toolkit integrates with:
- **Admin Navigation**: Automatic DEV_MODE-gated menu integration
- **Logging System**: Shared logging infrastructure with other maintenance tasks
- **Security Framework**: Consistent DEV_MODE access control
- **Task Architecture**: Follows established pattern for system management tasks

## Monitoring Capabilities

### Real-Time Assessment
- **Live Status**: Immediate system health evaluation without logging
- **Detailed Metrics**: Per-directory existence and permission status
- **Disk Space Tracking**: Current free space in bytes with threshold evaluation
- **Overall Health**: Comprehensive system status aggregation

### Historical Tracking
- **Execution History**: Complete log of all health check runs
- **Trend Analysis**: Historical view of system health over time
- **Issue Detection**: Identification of recurring or persistent problems
- **Audit Trail**: Complete record of system health assessments

## System Requirements

### Directory Structure
The health check verifies these directories relative to project root:
- **logs**: System logging directory
- **temp**: Temporary file storage
- **sessions**: Session data storage
- **backups**: Backup file storage
- **search_index**: Search index data storage

### Disk Space Requirements
- **Minimum Threshold**: 100MB free space required
- **Monitoring**: Continuous tracking of available space
- **Alerting**: Failed status when below threshold
- **Planning**: Early warning system for disk space issues

## Security Considerations

### Current Implementation
- **Safe Operations**: Only read-only file system checks, no modifications
- **No Sensitive Data**: Does not access or expose credentials or sensitive information
- **DEV_MODE Restriction**: Complete protection from production exposure
- **Controlled Scope**: Limited to predefined system directories

### Risk Assessment
- **Minimal Risk**: Read-only operations with no system modification capabilities
- **Information Disclosure**: Limited to directory structure and disk space metrics
- **Access Control**: Strict DEV_MODE gating prevents unauthorized access
- **System Impact**: No performance impact or resource consumption concerns

## Development Benefits

### System Reliability
- **Proactive Monitoring**: Early detection of system configuration issues
- **Environment Validation**: Verification of proper system setup
- **Deployment Confidence**: Pre-deployment health verification
- **Issue Prevention**: Early warning system for potential problems

### Operational Insights
- **System Visibility**: Clear view of system health and configuration
- **Troubleshooting Support**: Detailed diagnostics for issue resolution
- **Monitoring Integration**: Foundation for automated health monitoring
- **Documentation**: Complete audit trail of system health assessments