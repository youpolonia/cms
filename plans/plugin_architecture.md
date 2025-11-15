# Plugin Architecture Specification

## Directory Structure
```
plugins/
  sample_plugin/          # Sample plugin template
    init.php             # Required - Main initialization file
    functions.php        # Optional - Helper functions
    plugin.json          # Required - Plugin manifest
  existing_plugins/      # Existing plugins maintain current structure
```

## plugin.json Schema
```json
{
  "name": "PluginName",
  "version": "1.0.0",
  "description": "Plugin description",
  "author": "Author Name",
  "requires": "1.0.0",
  "tenant_aware": false,
  "hooks": {
    "action": ["init", "admin_init"],
    "filter": ["content_filter"]
  }
}
```

## File Templates

### init.php (required)
```php
<?php
// Plugin initialization
defined('CMS_ROOT') or die('Direct access denied');

function pluginname_init() {
    if (plugin_is_tenant_aware() && !current_tenant_supported()) {
        return;
    }
    // Initialization code
}
```

### functions.php (optional)
```php
<?php
// Optional helper functions
defined('CMS_ROOT') or die('Direct access denied');

function pluginname_utility_function() {
    // Helper function implementation
}
```

## Loading Sequence
1. CMS scans plugins/ directory
2. For each valid plugin:
   - Reads plugin.json
   - Includes init.php
   - Includes functions.php if exists
3. Registers hooks from plugin.json

## Tenant Awareness
- Controlled by plugin.json "tenant_aware" flag
- Tenant context available via:
  - `plugin_is_tenant_aware()` - Checks if plugin supports tenancy
  - `current_tenant_supported()` - Checks if current tenant is supported

## Backward Compatibility
- Existing plugins continue working with current structure
- New plugins use standardized structure
- System detects both formats

## Sample Plugin Implementation
```
plugins/
  sample_plugin/
    init.php
    plugin.json
    functions.php