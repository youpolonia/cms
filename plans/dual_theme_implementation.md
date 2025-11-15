# Dual Theme System Implementation Plan

## Directory Structure
```
public/
  themes/
    [theme_name]/
      theme.json
      layout.php
      style.css
      assets/
        js/
        css/
        images/

admin/
  themes/
    [theme_name]/
      theme.json
      layout.php
      style.css
      assets/
        js/
        css/
        images/
```

## ThemeManager Modifications
1. Add context parameter to key methods:
```php
public static function getActiveTheme(string $context = 'public'): string
public static function getActiveThemePath(string $context = 'public'): string
public static function setActiveTheme(string $themeName, string $context = 'public'): bool
```

2. Update database schema:
- Add `context` column to settings table for theme storage
- Modify queries to include context in WHERE clauses

3. Add validation methods:
```php
public static function validatePublicTheme(string $themeName): bool
public static function validateAdminTheme(string $themeName): bool
```

## Template Requirements
Public themes must include:
- `header.php` - Site header
- `footer.php` - Site footer
- `home.php` - Homepage template
- `page.php` - Default page template

## Testing Strategy
1. Unit tests for ThemeManager modifications
2. Integration tests for template rendering
3. Browser tests for theme switching
4. Fallback tests for database/session scenarios

## Implementation Phases
1. Directory structure setup
2. ThemeManager modifications
3. Admin interface updates
4. Testing and validation