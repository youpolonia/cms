# Plugin Development Guide

## Plugin Structure

Each plugin must have this basic structure:
```
plugin-name/
├── plugin.json       # Required manifest
├── includes/         # PHP classes and functions
├── assets/           # CSS/JS/images
├── templates/        # HTML/PHP templates  
└── languages/        # Translation files (optional)
```

## plugin.json Requirements

```json
{
  "name": "Plugin Name",
  "version": "1.0.0",
  "description": "Plugin description",
  "author": "Your Name",
  "license": "MIT",
  "requires": {
    "core": ">=1.2.0",
    "php": ">=8.1"
  },
  "hooks": {
    "init": "MyPlugin\\Init::setup",
    "admin_menu": "MyPlugin\\Admin::addMenu"
  }
}
```

Required fields:
- `name`: Plugin display name
- `version`: Semantic version (MAJOR.MINOR.PATCH)
- `description`: Brief plugin purpose
- `author`: Developer name/org
- `license`: SPDX license identifier

## Hook System Usage

Plugins interact with core via hooks:

```php
// Adding functionality to a hook
add_action('hook_name', 'callback_function', priority);

// Modifying data via filters
$value = apply_filters('filter_name', $value, $args);

// Creating custom hooks
do_action('custom_hook', $args);
```

Example plugin class:
```php
namespace MyPlugin;

class Core {
    public static function init() {
        add_action('template_header', [self::class, 'addStyles']);
        add_filter('page_title', [self::class, 'modifyTitle']);
    }
    
    public static function addStyles() {
        echo '<link rel="stylesheet" href="/plugins/my-plugin/assets/style.css">';
    }
    
    public static function modifyTitle($title) {
        return $title . ' | My Plugin';
    }
}
```

## Best Practices

1. **Namespacing**: Use unique namespace matching plugin name
2. **File Organization**:
   - Keep business logic in `/includes`
   - Store views in `/templates` 
   - Place assets in `/assets`
3. **Security**:
   - Validate all inputs
   - Escape all outputs
   - Use nonces for admin actions
4. **Performance**:
   - Load assets only when needed
   - Use hooks efficiently
   - Cache expensive operations