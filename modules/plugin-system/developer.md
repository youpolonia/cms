# Plugin System Developer Documentation

## Overview
The plugin system allows extending CMS functionality through modular plugins. Each plugin runs in a sandboxed environment with controlled access to core systems.

## Plugin Structure
```
/plugins/
  example-plugin/
    plugin.php      - Main plugin file (required)
    assets/         - CSS/JS/Images
    includes/       - Additional PHP files
    templates/      - View templates
```

## Plugin Metadata
Each plugin must declare metadata in its main file header:
```php
<?php
/*
Plugin Name: Example Plugin
Version: 1.0
Description: Adds example functionality
Requires CMS: 1.2+
*/
```

## Hooks System
Plugins can interact with the CMS through hooks:

### Actions
```php
// Adding an action
$hookSystem->addAction('init', function() {
    // Runs during system initialization
}, 10);

// Creating custom actions
$hookSystem->doAction('custom_event', $arg1, $arg2);
```

### Filters
```php
// Adding a filter
$hookSystem->addFilter('content_output', function($content) {
    return strtoupper($content);
});

// Applying filters
$filtered = $hookSystem->applyFilters('content_output', $rawContent);
```

## Best Practices
1. **Prefix everything**: Use unique prefixes for functions, classes, hooks
2. **Error handling**: Wrap risky operations in try/catch
3. **Security**: Never trust user input, escape all output
4. **Performance**: Load resources only when needed
5. **Compatibility**: Check CMS version before using features

## Example Plugin
```php
<?php
/*
Plugin Name: Hello World
Version: 1.0
*/

$hookSystem->addAction('init', function() use ($hookSystem) {
    $hookSystem->addFilter('page_title', function($title) {
        return $title . ' - Hello World Plugin';
    });
});