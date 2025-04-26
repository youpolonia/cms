# Template Variables Documentation

## System Variables

### `user_name`
The full name of the currently logged in user. Available in all templates.

### `user_email`
The email address of the currently logged in user. Available in all templates.

### `current_date`
The current date in YYYY-MM-DD format. Automatically updates when content is rendered.

### `current_time` 
The current time in 24-hour HH:MM format. Automatically updates when content is rendered.

### `content_title`
The title of the current content item. Only available in content templates.

### `content_url`
The public URL of the current content item. Only available in content templates.

### `category_name`
The name of the current content category. Only available in category templates.

## Notification Variables

### `notification_title`
The title/headline of the notification.

### `notification_message`
The full text content of the notification.

### `notification_priority`
The priority level of the notification (low, medium, high).

### `notification_date`
The date when the notification was created.

### `notification_url`
Optional URL associated with the notification.

## Special Variables

### `system_name`
The configured name of your CMS instance.

### `system_url`
The base URL of your CMS installation.

### `unread_count`
The current user's count of unread notifications.

### `archive_url`
Link to the notification archive for the current user.