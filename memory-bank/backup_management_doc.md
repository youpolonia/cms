# Backup Management Toolkit Documentation

## Overview

The Backup Management toolkit provides development and testing utilities for creating and managing system backups in the CMS. The core component is `BackupTask`, which handles automated creation of timestamped backup archives containing critical system files to ensure data protection and recovery capabilities.

### BackupTask Behavior

- **Purpose**: Creates timestamped ZIP archives of config and memory-bank directories
- **Normal Operation**: Runs manually via admin interface or as part of maintenance routines
- **Archive Contents**: Non-recursive backup of config files and memory-bank documentation
- **Storage Location**: Saves backups to `/var/www/html/cms/backups` directory
- **Logging**: All operations are logged to `logs/migrations.log` with timestamps and filenames

## Admin Navigation Links

The Backup Management section in the admin navigation (DEV_MODE only) provides four key functions:

### 1. Run Backup
- **Endpoint**: `/admin/test-backup/run_backup.php`
- **Function**: Manually triggers the BackupTask to create a new backup
- **Output**: JSON response with execution status (ok/failed)
- **Use Case**: On-demand backup creation for testing, maintenance, or pre-deployment safety

### 2. Backup Logs
- **Endpoint**: `/admin/test-backup/backup_logs.php`
- **Function**: Displays recent log entries related to backup operations
- **Output**: Plain text log viewer with timestamps and backup filenames
- **Use Case**: Monitoring backup activity and troubleshooting backup issues

### 3. Backup Status
- **Endpoint**: `/admin/test-backup/backup_status.php`
- **Function**: Reports current backup directory usage statistics
- **Output**: JSON with directory existence, file count, and total size in bytes
- **Use Case**: Monitoring backup storage usage and planning backup retention

### 4. Clear Backups
- **Endpoint**: `/admin/test-backup/clear_backups.php`
- **Function**: Removes all backup files from the backups directory
- **Output**: JSON with count of successfully removed files
- **Use Case**: Cleanup of old backups for testing or storage management

## Security and Access Control

### DEV_MODE Gating
All backup management endpoints are strictly gated by `DEV_MODE`:
- Immediate HTTP 403 response if `DEV_MODE` is not enabled
- No functionality exposed in production environments
- Admin navigation links only render when `DEV_MODE === true`

### File System Access
- Backup creation targets `/var/www/html/cms/config` and `/var/www/html/cms/memory-bank`
- Storage operations target `/var/www/html/cms/backups` directory only
- Uses safe file operations with `@glob()` and `@unlink()`
- Handles missing directories gracefully with automatic creation
- Non-recursive operations to prevent accidental deep directory access

## Logging Behavior

Backup management operations are logged to `logs/migrations.log`:
- **Success Format**: `[TIMESTAMP] BackupTask executed ok (file=backup_YYYYmmdd_His.zip)`
- **Failure Format**: `[TIMESTAMP] BackupTask executed failed`
- **Append Mode**: New entries added without overwriting existing logs
- **Content**: Includes execution timestamp, status, and backup filename on success
- **Integration**: Uses same logging infrastructure as other scheduled tasks

## Usage Scenarios

### 1. Routine Backups
- Create regular backups via Run Backup before major changes
- Monitor backup storage via Status endpoint to track accumulation
- Review logs to verify backup success and identify any failures

### 2. Development Testing
- Create clean backup before experimental changes
- Clear old backups via Clear Backups for storage management
- Monitor backup creation patterns through Status endpoint

### 3. System Monitoring
- Regular Status checks to monitor backup storage usage
- Log analysis to identify backup frequency and success rates
- Storage planning based on backup size growth trends

## Technical Implementation

### Backup Creation
- **Archive Format**: ZIP compression using PHP's ZipArchive class
- **Filename Pattern**: `backup_YYYYmmdd_His.zip` with timestamp
- **Content Structure**: `config/` and `memory-bank/` directories preserved
- **File Detection**: Uses `is_file()` to include only regular files
- **Error Handling**: Graceful failure if ZipArchive unavailable

### File Operations
- **Detection**: Uses `is_dir()` and `is_readable()` for directory validation
- **Enumeration**: `@glob()` for safe file listing without recursion
- **Removal**: `@unlink()` with error suppression for graceful cleanup
- **Storage**: Automatic backup directory creation with proper permissions

### Error Handling
- **Missing Dependencies**: Fails gracefully if ZipArchive class unavailable
- **Directory Issues**: Handles missing or unreadable directories safely
- **File Permissions**: Uses error suppression for robust file operations
- **JSON Responses**: Consistent API formatting for all endpoints

### Performance Considerations
- **Non-recursive**: Limits backup to top-level files for safety and speed
- **Minimal Memory**: File-by-file processing without loading entire directories
- **Atomic Operations**: Each backup is a single atomic ZIP creation
- **Efficient Logging**: Append-only writes to minimize I/O impact

## Integration Notes

The Backup Management toolkit integrates with:
- **Admin Navigation**: Automatic DEV_MODE-gated menu integration
- **Logging System**: Shared logging infrastructure with other maintenance tasks
- **Security Framework**: Consistent DEV_MODE access control
- **Error Handling**: Standard HTTP response codes and JSON formatting

## Backup Content Policy

### Included Directories
- **Config Files**: All files in `/var/www/html/cms/config` (non-recursive)
- **Memory Bank**: All files in `/var/www/html/cms/memory-bank` (non-recursive)
- **Exclusions**: No database dumps, user uploads, or temporary files
- **Structure**: Preserves directory structure within ZIP archive

### File Selection
- **Regular Files Only**: Uses `is_file()` to exclude directories and links
- **Top-Level Only**: Non-recursive to avoid deep system file inclusion
- **Error Tolerance**: Continues backup creation even if some files are unreadable
- **Timestamp Preservation**: Maintains original file modification times in archive