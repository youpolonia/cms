# Notification System Deployment Guide

## Feature Overview
The notification system provides:
- User notifications with read receipts
- Template-based notification creation
- Multi-channel delivery (in-app, email)
- Scheduled notifications
- User preference management

## Deployment Steps

### Database Setup
1. Ensure these tables exist with proper permissions:
   - `notifications` (core table)
   - `read_receipts` (tracks notification reads)
   - `notification_templates` (stores templates)

2. Required indexes:
   ```sql
   CREATE INDEX idx_notifications_user ON notifications(user_id);
   CREATE INDEX idx_read_receipts_notification ON read_receipts(notification_id);
   ```

### File Deployment
1. Copy these directories to production:
   - `admin/notifications/`
   - `api/notifications/`

2. Required permissions:
   - `admin/notifications/save_preferences.php` (write for web server)
   - `admin/notifications/schedule.php` (execute permission)

### Configuration
1. Set these in your config:
   ```php
   define('NOTIFICATION_MAX_RETENTION_DAYS', 30);
   define('NOTIFICATION_QUEUE_ENABLED', true);
   ```

## Known Limitations
1. No built-in SMS support
2. Template variables must be pre-defined
3. Maximum 10 concurrent scheduled notifications
4. Read receipts only track first read (no re-read tracking)

## Future Enhancements
1. Webhook integration
2. Notification grouping
3. Advanced scheduling options
4. SMS channel support
5. Notification analytics