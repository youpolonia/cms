# AI Plugin System

## Overview
The AI Plugin System allows extending CMS functionality through modular plugins. Plugins can:
- Register analyzers (content processing)
- Register transformers (content modification)
- Hook into system events

## Plugin Structure
Each plugin requires:
```
plugin-name/
  ├── plugin.json (manifest)
  ├── Analyzers/ (optional)
  ├── Transformers/ (optional)
  └── assets/ (optional)
```

## Registration Process
1. **Manifest Requirements** (`plugin.json`):
```json
{
  "name": "Example Plugin",
  "version": "1.0.0",
  "mainClass": "ExamplePlugin",
  "dependencies": []
}
```

2. **Required Methods**:
```php
class ExamplePlugin {
  public static function getRegisteredAnalyzers(): array {
    return ['analyzer1' => 'AnalyzerClass::method'];
  }

  public static function getRegisteredTransformers(): array {
    return ['transformer1' => 'TransformerClass::method'];
  }
}
```

## FTP Deployment
1. Upload plugin directory to `/admin/plugins/`
2. System auto-detects new plugins on next request
3. No CLI or manual activation required

## Security Considerations
- All plugin methods must be static
- No direct filesystem access allowed
- Input/output automatically sanitized