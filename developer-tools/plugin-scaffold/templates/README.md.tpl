# {{name}}

{{description}}

## Installation
1. Copy the plugin folder to `/plugins/`
2. Add the plugin to your CMS configuration

## Configuration
Edit `config.json` to customize plugin behavior:

```json
{
    "name": "{{name}}",
    "version": "{{version}}",
    // Other configuration options
}
```

## Usage
```php
// Initialize the plugin
$plugin = new {{class_name}}();
$plugin->init();
```

## Changelog
### {{version}} - {{date}}
- Initial release