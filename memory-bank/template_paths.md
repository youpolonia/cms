# Template Path Resolution System

## Overview
The template system provides standardized path resolution for including PHP templates across the CMS. It handles:
- Absolute paths
- Relative paths
- Fallback hierarchy
- Automatic .php extension handling

## Search Paths
Templates are searched in this order:
1. `templates/`
2. `views/` 
3. `admin/views/`

Additional paths can be added via `Template::addSearchPath()`

## Resolution Rules
1. If path exists as-is (absolute path), use it
2. Check relative to each search path:
   - `{search_path}/{template_name}`
   - `{search_path}/{template_name}.php` (if no extension)
3. First match found is used

## Usage
```php
// Basic usage
Template::render('header', $data);

// With explicit path
Template::render('admin/dashboard.php', $data);

// Add custom search path
Template::addSearchPath('custom/templates');
```

## Fallback Hierarchy
If template isn't found:
1. Check same path in default theme (`themes/default/`)
2. Check CMS core templates (`core/templates/`)
3. Throw RuntimeException if still not found

## Best Practices
- Prefer relative paths without extensions
- Group related templates in subdirectories
- Use `Template::render()` instead of direct includes