# Scheduling System Usage Examples

## Basic Scheduling
```php
// Schedule content publication
$schedule = new Schedule();
$schedule->content_id = 123;
$schedule->action = 'publish';
$schedule->schedule_time = '2025-05-12 09:00:00';
$schedule->save();
```

## Recurring Content Updates
```php
// Update content every Monday at 8am
$schedule = new Schedule();
$schedule->content_id = 456;
$schedule->action = 'update';
$schedule->schedule_time = '2025-05-12 08:00:00';
$schedule->recurrence = 'weekly 1'; // Weekly on Monday
$schedule->save();
```

## Best Practices
1. Always validate schedule times are in the future
2. Set up notifications for failed schedules
3. Use the admin interface for complex recurring schedules

## Troubleshooting
**Issue:** Schedules not executing
- Check cron job is running
- Verify notification.php config
- Review error logs in `storage/logs/scheduling.log`