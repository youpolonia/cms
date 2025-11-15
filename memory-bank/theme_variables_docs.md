# Theme Variables System Documentation

## Overview
Global theme variables system allowing per-theme configuration with:
- Tenant/theme isolation
- Type-safe values (string, number, boolean, JSON)
- Admin UI for management
- Template injection

## Components

### 1. Database Schema
```sql
CREATE TABLE theme_variables (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenant_id VARCHAR(64) NOT NULL,
  theme_id VARCHAR(64) NOT NULL,
  variable_name VARCHAR(255) NOT NULL,
  variable_value TEXT,
  variable_type ENUM('string','number','boolean','json') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY (tenant_id, theme_id, variable_name)
);
```

### 2. ThemeVariableManager
Location: `includes/ThemeVariableManager.php`

Key Methods:
- `get_theme_variable(string $name, mixed $default = null): mixed`
- `set_theme_variable(string $name, mixed $value, string $type = 'auto'): bool`
- `delete_theme_variable(string $name): bool`
- `get_all_theme_variables(): array`

### 3. Admin Interface
Location: `admin/themes/variables.php`

Features:
- CRUD operations for theme variables
- Type selector with validation
- JSON editor for complex values
- Tenant/theme scope selection

### 4. Template Integration
Variables are injected into templates via:
```php
$GLOBALS['theme_vars'] = ThemeVariableManager::get_all_theme_variables();
```

Access in templates:
```html
<!-- String variable -->
<h1><?= $theme_vars['header_text'] ?></h1>

<!-- JSON variable -->
<script>
const config = <?= $theme_vars['js_config'] ?>;
</script>
```

### 5. Testing
Test coverage includes:
- Basic CRUD operations
- Type validation
- Tenant/theme isolation
- Template injection
- Edge cases (missing vars, invalid JSON)

Location: `tests/ThemeVariableManagerTest.php`