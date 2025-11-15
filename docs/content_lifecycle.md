# Content Lifecycle Manager Documentation

## Overview
The `ContentLifecycleManager` class handles content status transitions and automated updates based on publish/expiry dates.

## Status Constants
The class defines these content status constants:
- `STATUS_DRAFT`: Initial state for unpublished content
- `STATUS_SCHEDULED`: Content scheduled for future publication  
- `STATUS_PUBLISHED`: Currently published content
- `STATUS_ARCHIVED`: Manually archived content
- `STATUS_EXPIRED`: Automatically expired content

## Public Methods

### `validateTransition(string $currentStatus, string $newStatus): bool`
Validates if a status transition is allowed.

**Parameters:**
- `$currentStatus`: Current status of the content (must be one of the status constants)
- `$newStatus`: Desired new status to transition to

**Returns:**  
`true` if transition is valid, `false` otherwise

**Example:**
```php
$isValid = ContentLifecycleManager::validateTransition(
    ContentLifecycleManager::STATUS_DRAFT,
    ContentLifecycleManager::STATUS_SCHEDULED
); // Returns true
```

### `updateStatusAutomatically(array &$content): void`
Automatically updates content status based on publish/expiry dates.

**Parameters:**
- `$content`: Reference to content array containing:
  - `status`: Current status
  - `publish_date`: Scheduled publication date (optional)
  - `expiry_date`: Expiration date (optional)

**Behavior:**
- If content is `STATUS_SCHEDULED` and publish date has passed, updates to `STATUS_PUBLISHED`
- If content is `STATUS_PUBLISHED` and expiry date has passed, updates to `STATUS_EXPIRED`

**Example:**
```php
$content = [
    'status' => ContentLifecycleManager::STATUS_SCHEDULED,
    'publish_date' => '2025-05-21 12:00:00'
];
ContentLifecycleManager::updateStatusAutomatically($content);
```

## Status Transition Rules
Valid transitions between statuses:

- `DRAFT` → `SCHEDULED`
- `SCHEDULED` → `PUBLISHED` or back to `DRAFT`
- `PUBLISHED` → `ARCHIVED` or `EXPIRED`

## Error Handling
- Invalid status values will result in `validateTransition()` returning false
- Missing or invalid dates in `updateStatusAutomatically()` will be safely ignored
- Always validate transitions before attempting status changes

## Automated Status Updates
The system automatically handles these transitions:
- Scheduled → Published when publish date is reached
- Published → Expired when expiry date is reached

Run `updateStatusAutomatically()` periodically (e.g., via cron job) to process pending status updates.