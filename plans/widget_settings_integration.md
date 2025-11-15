# Widget Settings Integration Plan

## Schema Extension
```json
{
  "settings": {
    "widgets": {
      "UserActivityWidget": {
        "show_avatars": true,
        "max_items": 10,
        "time_format": "relative"
      },
      "StatisticsWidget": {
        "show_charts": true,
        "refresh_interval": 300
      },
      "SystemStatusWidget": {
        "show_uptime": true,
        "show_memory": false
      },
      "RecentContentWidget": {
        "max_items": 5,
        "show_thumbnails": true
      }
    }
  }
}
```

## Implementation Steps

1. **Extend theme.template.json**
   - Add widget settings schema
   - Include default configurations for all core widgets

2. **Modify ThemeBuilder**
   - Update `createTheme()` to include default widget configs
   - Add validation for widget settings during import/export

3. **Create WidgetSettingsManager**
```php
class WidgetSettingsManager {
    public static function getSettings(string $widgetClass, array $themeSettings): array;
    public static function validateSettings(array $settings): bool;
    public static function getDefaults(): array;
}
```

4. **Widget Class Modifications**
   - Each widget should:
     - Define default settings
     - Accept theme settings
     - Handle missing settings gracefully

## Validation Rules
- JSON schema validation for widget settings
- Each widget provides its own schema fragment
- ThemeBuilder validates against schema during import