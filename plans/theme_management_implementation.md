# Theme Management Implementation Plan

## Core Components

1. **ThemeManager Class** (`includes/thememanager.php`)
```php
class ThemeManager {
    public static function getAvailableThemes(): array {
        // Scan themes directory and return available themes
    }
    
    public static function getActiveTheme(): string {
        // Get from settings or default
    }
    
    public static function renderLayout(string $template, array $data = []): string {
        // Render theme template with data
    }
    
    public static function getThemeMetadata(string $themeId): array {
        // Parse theme.json for metadata
    }
}
```

2. **Admin Interface** (`public/admin/themes.php`)
- List available themes
- Theme activation/deactivation
- Version management (using ThemeStorageHandler)

3. **Frontend Integration** (`public/index.php`)
```php
// Initialize theme
$theme = ThemeManager::getActiveTheme();
$content = ThemeManager::renderLayout('main', $pageData);
```

## Implementation Steps

1. Create ThemeManager with core methods
2. Build admin interface using SettingsModel
3. Integrate with existing ThemeStorageHandler
4. Update frontend to use ThemeManager
5. Add documentation in `api-docs/themes.md`

## Testing Plan

1. Unit tests for ThemeManager methods
2. Integration test for theme switching
3. UI test for admin interface