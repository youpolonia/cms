# Widget Region System

## Theme-Specific Widget Regions

### Overview
The CMS now supports theme-specific widget regions, allowing different widget configurations per theme while maintaining backward compatibility with existing regions.

### Usage

#### Registering Theme Regions
```php
ThemeManager::registerWidgetRegion('sidebar', 'my-theme');
```

#### Rendering Regions
```php
// Theme-specific region
echo render_widget_region('sidebar', 'my-theme');

// Default region (backward compatible)
echo render_widget_region('sidebar');
```

#### Database Schema
Widget region bindings now include a `theme` column (nullable):
- `NULL` = applies to all themes
- `theme_name` = applies only to specified theme

### Backward Compatibility
- All existing widget regions continue to work without modification
- Theme parameter is optional in all methods
- When no theme specified, system uses:
  1. Theme-specific bindings for current theme (if any)
  2. Default bindings (theme = NULL)

## Widget Layout Customization

### Overview
The WidgetLayoutManager provides advanced control over widget positioning and visibility.

### Features
- Position sorting (priority-based ordering)
- Conditional visibility rules
- Theme-aware layout configurations

### Usage Examples
```php
// Set widget position priority
WidgetLayoutManager::setPosition('sidebar-widget', 5);

// Add visibility condition
WidgetLayoutManager::addVisibilityCondition(
    'sidebar-widget',
    function() { return userHasPermission('view_widget'); }
);

// Get visible widgets for current context
$visibleWidgets = WidgetLayoutManager::getVisibleWidgets('sidebar');
```

### Testing
The WidgetLayoutManager test suite verifies:
- Position sorting correctness
- Visibility condition evaluation
- Theme-specific layout handling
- Backward compatibility