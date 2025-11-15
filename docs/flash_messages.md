# Flash Message System

## Overview
The FlashMessage service provides a standardized way to display temporary status messages to users after workflow actions or other operations.

## Basic Usage

```php
// Add a flash message
FlashMessage::add(FlashMessage::TYPE_SUCCESS, 'Operation completed successfully');

// Add a workflow-specific message
FlashMessage::approve('Content approved', ['content_id' => 123]);

// Retrieve and display messages in view
$flash_messages = FlashMessage::get();
include 'admin/views/includes/flash_messages.php';
```

## Message Types

| Type | Description |
|------|-------------|
| success | Positive confirmation |
| error | Critical failure |
| warning | Potential issue |
| info | General information |
| submit | Workflow submission |
| approve | Workflow approval |
| reject | Workflow rejection |

## Workflow Helpers

```php
// Submit action
FlashMessage::submit('Content submitted for review');

// Approve action 
FlashMessage::approve('Content approved', [
    'approver' => $user->name,
    'timestamp' => time()
]);

// Reject action
FlashMessage::reject('Content rejected', [
    'reason' => 'Incomplete information',
    'contact' => 'editor@example.com'
]);
```

## View Integration

Include this in your view template:
```php
include 'admin/views/includes/flash_messages.php';
```

The template automatically handles:
- HTML escaping
- Data display
- Message styling by type

## Backward Compatibility

Legacy code can continue using:
```php
FlashMessage::legacyAdd('success', 'Old style message');