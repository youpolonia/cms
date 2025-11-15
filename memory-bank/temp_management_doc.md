# Temp Management Toolkit Documentation

## Overview

The Temp Management toolkit provides development and testing utilities for managing temporary files in the CMS. The core component is `TempCleanerTask`, which handles automated cleanup of expired temporary files to prevent disk space issues and maintain optimal performance.

### TempCleanerTask Behavior

- **Purpose**: Removes temporary files older than 7 days from the `/var/www/html/cms/temp` directory
- **Normal Operation**: Runs as part of scheduled maintenance, cleaning up expired temp files automatically
- **Manual Operation**: Can be triggered manually via admin interface for testing or immediate cleanup
- **Logging**: All operations are logged to `logs/migrations.log` with timestamps and removal counts

## Admin Navigation Links

The Temp Management section in the admin navigation (DEV_MODE only) provides four key functions:

### 1. Run Temp Cleaner
- **Endpoint**: `/admin/test-temp/run_temp_cleaner.php`
- **Function**: Manually triggers the TempCleanerTask
- **Output**: JSON response with execution status (ok/failed)
- **Use Case**: On-demand temp file cleanup for testing or maintenance

### 2. Temp Cleaner Logs
- **Endpoint**: `/admin/test-temp/temp_cleaner_logs.php`
- **Function**: Displays recent log entries related to temp cleaning operations
- **Output**: Plain text log viewer with timestamps and operation details
- **Use Case**: Monitoring temp cleanup activity and troubleshooting

### 3. Temp Cleaner Status
- **Endpoint**: `/admin/test-temp/temp_cleaner_status.php`
- **Function**: Reports current temp directory usage statistics
- **Output**: JSON with directory existence, file count, and total size
- **Use Case**: Monitoring temp storage usage and validating cleanup effectiveness

### 4. Clear Temp
- **Endpoint**: `/admin/test-temp/clear_temp.php`
- **Function**: Immediately removes all temp files regardless of age
- **Output**: JSON with removal count
- **Use Case**: Complete temp directory reset for testing scenarios

## Security and Access Control

### DEV_MODE Gating
All temp management endpoints are strictly gated by `DEV_MODE`:
- Immediate HTTP 403 response if `DEV_MODE` is not enabled
- No functionality exposed in production environments
- Admin navigation links only render when `DEV_MODE === true`

### File System Access
- Operations target `/var/www/html/cms/temp` directory only
- Uses safe file operations with `@glob()` and `@unlink()`
- Handles missing directories gracefully
- No recursive operations to prevent accidental system file access

## Logging Behavior

Temp management operations are logged to `logs/migrations.log`:
- **Format**: `[TIMESTAMP] TempCleanerTask executed ok/failed (removed=X)`
- **Append Mode**: New entries added without overwriting existing logs
- **Content**: Includes execution timestamp, status, and count of files processed
- **Integration**: Uses same logging infrastructure as other scheduled tasks

## Usage Scenarios

### 1. Routine Cleanup
- Monitor temp accumulation via Status endpoint
- Run manual cleanup when needed via Run Temp Cleaner
- Review logs to verify cleanup effectiveness

### 2. Development Testing
- Clear all temp files via Clear Temp for clean test state
- Monitor temp file creation during testing via Status endpoint
- Verify cleanup behavior through Logs endpoint

### 3. System Monitoring
- Regular Status checks to monitor disk usage
- Log analysis to identify temp file patterns
- Performance impact assessment of temp file accumulation

## Technical Implementation

### File Operations
- **Detection**: Uses `is_dir()` and `is_readable()` for directory validation
- **Enumeration**: `@glob()` for safe file listing
- **Removal**: `@unlink()` with error suppression for graceful handling
- **Age Check**: Uses `filemtime()` to determine file age (7-day threshold)

### Error Handling
- Graceful degradation when directories don't exist
- Safe file operations with error suppression
- JSON error responses for API consistency
- No fatal errors or system disruption

### Performance Considerations
- Non-recursive operations for safety
- Minimal memory footprint with file-by-file processing
- No blocking operations that could impact user experience
- Efficient logging with append-only writes

## Integration Notes

The Temp Management toolkit integrates with:
- **Admin Navigation**: Automatic DEV_MODE-gated menu integration
- **Logging System**: Shared logging infrastructure with other scheduled tasks
- **Security Framework**: Consistent DEV_MODE access control
- **Error Handling**: Standard HTTP response codes and JSON formatting

## File Age Policy

### 7-Day Retention
- **Threshold**: Files older than 604800 seconds (7 days) are eligible for cleanup
- **Calculation**: Uses `time() - filemtime($file)` for age determination
- **Safety**: More conservative than session files (24 hours) to account for longer-running processes
- **Override**: Clear Temp function removes all files regardless of age for testing needs