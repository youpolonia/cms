# Notification System Documentation

## Core Architecture

The notification system is a modular component of the CMS that handles:
- Creation and management of notifications
- User notification preferences
- Scheduled notifications
- Multi-channel delivery (email, SMS, webhook, in-app)

### Key Components
1. **NotificationManager** - Core class for notification operations
2. **API Endpoints** - RESTful interfaces for client interactions
3. **Admin Interface** - Management console for notifications
4. **Database Schema** - Stores notification data and preferences
5. **JSON Queue** - Temporary storage for pending notifications

## NotificationManager API Reference

```php
class NotificationManager {
    /**
     * Queues a new notification
     * @param string $type Notification type (info|warning|error|system)
     * @param string $message Notification content
     * @param array $context Additional metadata
     * @return bool True on success
     */
    public static function queueNotification(string $type, string $message, array $context = []): bool;

    /**
     * Gets all queued notifications
     * @return array Array of notification objects
     */
    public static function getQueuedNotifications(): array;

    /**
     * Clears a specific notification
     * @param string $id Notification ID
     * @return bool True on success
     */
    public static function clearNotification(string $id): bool;
}
```

## JSON Storage Format

Notifications are stored in `logs/notifications.json` with this structure:
```json
[
    {
        "id": "unique_id",
        "type": "info|warning|error|system",
        "message": "HTML-escaped content",
        "context": {},
        "timestamp": 1234567890,
        "read": false
    }
]
```

## Security Considerations
- All user-provided content is HTML-escaped
- Type validation restricts to predefined values
- Context data is sanitized recursively
- File permissions should restrict access to notifications.json

## Usage Examples

```php
// Queue a notification
NotificationManager::queueNotification(
    'warning',
    'Disk space running low',
    ['disk' => 'C:', 'free' => '5%']
);

// Get all notifications
$notifications = NotificationManager::getQueuedNotifications();

// Clear a notification
NotificationManager::clearNotification('abc123');
```

## Admin Interface Guide

The admin interface provides:
- Notification listing with filters
- Bulk actions (mark as read, delete)
- Scheduled notification creation
- Channel configuration
- User preference management