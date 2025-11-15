# Theme Manager v1 Integration Documentation

## ThemeRegistry.php

### Purpose
Central registry for all available themes and their configurations.

### Public Methods
```php
/**
 * Register a new theme configuration
 * @param string $themeName 
 * @param array $config Theme configuration
 */
public static function register(string $themeName, array $config): void

/**
 * Get theme configuration by name
 * @param string $themeName
 * @return array|null Theme config or null if not found
 */
public static function getConfig(string $themeName): ?array
```

### Theme.json Example
```json
{
  "name": "default",
  "styles": {
    "global": "body { font-family: Arial; }",
    "text": ".text-block { color: #333; }"
  }
}
```

### Integration Points
- Called by ThemeLoader during initialization
- Accessed by ThemeContext for current theme resolution

---

## ThemeLoader.php

### Purpose
Loads and validates theme configurations from JSON files.

### Public Methods
```php
/**
 * Load theme from JSON file
 * @param string $path Path to theme.json
 * @throws InvalidThemeException On validation failure
 */
public static function loadFromFile(string $path): void

/**
 * Validate theme configuration structure
 * @param array $config
 * @throws InvalidThemeException
 */
public static function validate(array $config): void
```

### Error Handling
- Falls back to default theme if loading fails
- Logs errors to system log

---

## ThemeContext.php

### Purpose
Manages current theme context and inheritance.

### Public Methods
```php
/**
 * Set current theme context
 * @param string|null $contextName
 */
public static function setContext(?string $contextName): void

/**
 * Get styles for current context
 * @return array Merged styles with inheritance
 */
public static function getStyles(): array
```

### Legacy Compatibility
- Maintains backward compatibility with v2.1 theme presets
- Automatically converts old preset format

---

## BlockRenderer.php Modifications

### Changes
```php
// Old
public function render(array $block): string

// New  
public static function render(array $block, ?string $context = null): string
```

### Style Application Logic
1. Gets theme from ThemeContext
2. Applies styles in order:
   - Global styles
   - Block-type specific styles
   - Block-level overrides

### Example Usage
```php
$html = BlockRenderer::render($block, 'article-page');