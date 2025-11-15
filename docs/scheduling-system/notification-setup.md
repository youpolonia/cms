# Scheduling System Notification Setup

## Notification Types
1. **Schedule Created** - When new schedule is added
2. **Schedule Triggered** - When scheduled action executes
3. **Schedule Failed** - When scheduled action fails
4. **Upcoming Schedule** - Reminder before scheduled time

## Configuration
Set notification preferences in `config/notification.php`:
```php
'scheduling' => [
    'enabled' => true,
    'channels' => ['email', 'dashboard'],
    'reminder_hours' => 1 // Send reminder 1 hour before
]
```

## Customizing Notifications
Override default templates by creating:
```
templates/notifications/scheduling/
  - created.php
  - triggered.php
  - failed.php
  - reminder.php
```

## Example Notification Class
```php
// See includes/models/ScheduleNotification.php
class ScheduleNotification {
    public function send($schedule, $type) {
        // Notification logic here
    }
}