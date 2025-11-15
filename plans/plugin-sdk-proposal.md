# Plugin SDK Architecture Proposal

## 1. Plugin Structure
```
/plugins/
  PluginName/
    plugin.json       - Manifest file (required)
    plugin.php       - Main plugin file (optional)
    bootstrap.php    - Initialization (optional)
    assets/          - Static resources
    includes/        - Additional PHP files
```

## 2. Manifest Format (plugin.json)
```json
{
  "name": "PluginName",
  "version": "1.0.0",
  "description": "Plugin description",
  "author": "Author Name",
  "requires": "1.0.0",  // Minimum CMS version
  "hooks": {
    "action": ["init", "admin_init"],
    "filter": ["content_before_render"]
  },
  "routes": {
    "/custom-path": "handler_function"
  },
  "settings": {
    "key": "default_value"
  }
}
```

## 3. Execution Sandboxing
- **Isolated Scope**: Each plugin runs in separate output buffer
- **Error Handling**: Plugin errors won't crash CMS
- **Security Controls**:
  - Restricted filesystem access
  - Limited PHP functions (via `disable_functions`)
  - Input/output sanitization

## 4. Plugin Types
1. **Class-based** (implements `PluginInterface`)
2. **Function-based** (via bootstrap callbacks)
3. **Hybrid** (class with bootstrap hooks)

## 5. Lifecycle Hooks
- `activate` - Plugin activation
- `deactivate` - Plugin deactivation
- `uninstall` - Cleanup on removal

## 6. Security Measures
- Manifest validation
- Code signing (optional)
- Permission system for:
  - Filesystem access
  - Database access
  - API calls

## Implementation Plan

1. Create `PluginSDK` class in `core/PluginSDK.php`
2. Update `PluginLoader` to support new manifest format
3. Implement sandboxing in `core/services/SecurityService.php`
4. Add sample plugin in `/plugins/HelloWorld/`
5. Document SDK in `docs/plugin-sdk.md`