# WidgetLayoutManager API Documentation

## Overview
The `WidgetLayoutManager` class handles widget positioning and visibility in the CMS. It provides static methods for:

- Sorting widgets by position
- Evaluating visibility conditions
- Filtering visible widgets

All methods are FTP-compatible with no CLI dependencies.

## Methods

### `sortWidgetsByPosition(array $widgets): array`

Sorts an array of widgets by their position attribute.

**Parameters:**
- `$widgets`: Array of widget data (must contain 'position' key)

**Returns:**  
Sorted array of widgets

**Behavior:**
- Widgets without position are placed first
- Invalid position values are treated as 0
- Original array is not modified

**Example:**
```php
$sorted = WidgetLayoutManager::sortWidgetsByPosition([
    ['id' => 1, 'position' => 3],
    ['id' => 2, 'position' => 1]
]);
```

### `checkVisibility(array $widget, array $context): bool`

Evaluates if a widget should be visible based on conditions.

**Parameters:**
- `$widget`: Widget data (may contain 'conditions')
- `$context`: Current user/application context

**Returns:**  
`true` if widget should be visible, `false` otherwise

**Behavior:**
- Widgets without conditions are always visible
- Conditions are evaluated as strict equality checks
- All conditions must match for visibility

**Example: 
```php
$visible = WidgetLayoutManager::checkVisibility(
    ['conditions' => ['role' => 'admin']],
    ['role' => 'user'] // Returns false
);
```

### `getVisibleWidgets(array $widgets, array $context): array`

Filters and returns visible widgets in position order.

**Parameters:**
- `$widgets`: Complete widget set
- `$context`: Current user/application context

**Returns:**  
Filtered and sorted array of visible widgets

**Behavior:**
- Combines `checkVisibility` and `sortWidgetsByPosition`
- Preserves original widget data
- Returns empty array for no matches

**Example:
```php
$visible = WidgetLayoutManager::getVisibleWidgets(
    $allWidgets,
    ['logged_in' => true]
);
```

## Edge Cases

| Case | Behavior |
|------|----------|
| Empty widgets array | Returns empty array |
| Missing position | Treated as position 0 |
| Invalid position | Treated as position 0 |
| No conditions | Widget is visible |
| Partial conditions | Only visible if all match |