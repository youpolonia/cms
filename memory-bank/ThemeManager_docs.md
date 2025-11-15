# ThemeManager Documentation

## Public Methods

### `loadThemeMetadata(string $themeName): ?array`
Loads and validates theme metadata from theme.json file.

**Parameters:**
- `$themeName` - Name of the theme directory

**Returns:**
- Array of theme metadata on success
- Null on failure

**Errors:**
- Triggers E_USER_WARNING if file not found or invalid JSON

---

### `validateTheme(string $themeName): bool`
Validates theme structure by checking for required files.

**Parameters:**
- `$themeName` - Name of the theme directory

**Returns:**
- True if all required files exist
- False if any files are missing

**Required Files:**
- theme.json
- layout.php
- style.css

---

### `applyTheme(string $themeName): bool`
Applies the specified theme by setting it in session.

**Parameters:**
- `$themeName` - Name of the theme to apply

**Returns:**
- True if theme was validated and applied
- False if validation failed

**Note:**
- Automatically calls validateTheme() before applying
- Sets $_SESSION['active_theme']