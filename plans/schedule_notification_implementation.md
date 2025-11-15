# ScheduleNotification Implementation Plan

## Model Requirements
1. File: `models/ScheduleNotification.php`
2. Constants:
```php
const TYPE_CREATED = 'created';
const TYPE_UPDATED = 'updated'; 
const TYPE_EXECUTING = 'executing';
const TYPE_COMPLETED = 'completed';
const TYPE_CONFLICT = 'conflict';
```
3. Properties matching `scheduled_notifications` table:
- schedule_id (primary key)
- notification_id
- worker_id  
- title
- message
- type (using above constants)
- scheduled_at
- status
- retry_count
- max_retries
- last_attempt

## Database Alignment
1. Update `scheduled_notifications.type` ENUM to match constants
2. Add migration to modify column:
```sql
ALTER TABLE scheduled_notifications 
MODIFY type ENUM('created','updated','executing','completed','conflict');
```

## Handler Updates
1. Update `OptimizedNotificationHandler.php` priority map
2. Verify foreign key constraints

## Implementation Steps
1. Create model file
2. Update database schema
3. Test with existing handlers