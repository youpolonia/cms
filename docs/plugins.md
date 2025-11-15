# Plugin System Developer Guide

## Overview
The CMS plugin system allows extending functionality without modifying core files. Plugins are self-contained packages that:
- Are discovered automatically in `/plugins/` directory
- Require a `plugin.json` manifest
- Use `bootstrap.php` for initialization
- Can register action and filter hooks

## Plugin Structure
```
/plugins/
  ExamplePlugin/
    plugin.json       - Required manifest
    bootstrap.php    - Main plugin file
    (other files)    - Additional assets/classes
```

## Manifest Requirements (`plugin.json`)
```json
{
    "name": "PluginName",
    "version": "1.0.0",
    "description": "Plugin description",
    "author": "Your Name",
    "requires": "1.0.0",  // Minimum CMS version
    "hooks": {
        "action": ["hook1", "hook2"],
        "filter": ["filter1"]
    }
}
```

## Hook System
### Available Core Hooks
- Actions:
  - `init` - Early initialization
  - `admin_init` - Admin panel initialization
  - `activate_[plugin]` - Plugin activation
  - `deactivate_[plugin]` - Plugin deactivation

- Filters:
  - `content_before_render` - Modify content before display
  - `title_before_render` - Modify page titles

### Registering Hooks
```php
// In bootstrap.php
Includes\Core\Plugin::addAction('init', function() {
    // Your initialization code
});

Includes\Core\Plugin::addFilter('content_before_render', function($content) {
    return str_replace('foo', 'bar', $content);
});
```

## Security Guidelines
1. Always validate user input
2. Sanitize output
3. Use prepared statements for database queries
4. Limit file system access
5. Verify nonces for admin actions

## Testing Checklist
- Verify plugin loads correctly
- Test all registered hooks
- Validate activation/deactivation
- Check for memory leaks
- Test with error logging enabled