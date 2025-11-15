# Plugin Block System

## Overview
The plugin block system allows developers to extend the CMS with custom content blocks. Each plugin block consists of:
- PHP handler class
- JavaScript editor integration
- JSON configuration
- Optional CSS styles

## 1. Block Registration Process

### PHP Registration
1. Create a class extending `BaseBlockHandler`
2. Implement required methods:
   ```php
   class MyPluginBlock extends BaseBlockHandler {
       public static function register(): void {
           BlockRegistry::addHandler('my-plugin', new self());
       }
       
       public function renderEdit(array $data): string {
           // Return editor HTML
       }
       
       public function renderPreview(array $data): string {
           // Return frontend HTML
       }
   }
   ```
3. Call registration in plugin init:
   ```php
   MyPluginBlock::register();
   ```

### JavaScript Registration
```javascript
BlockManager.registerType('my-plugin', {
    editComponent: MyEditComponent,
    previewComponent: MyPreviewComponent
});
```

## 2. JSON Schema Format

### Block Configuration
```json
{
  "type": "plugin/my-plugin",
  "config": {
    "version": "1.0.0",
    "permissions": ["edit_content"],
    "dependencies": {
      "css": ["/plugins/my-plugin/style.css"],
      "js": ["/plugins/my-plugin/editor.js"]
    }
  },
  "data": {
    // Custom block data structure
  }
}
```

### Editor Configuration
```json
{
  "toolbar": {
    "icon": "icon-class",
    "label": "My Plugin",
    "category": "media"
  },
  "settings": {
    "maxInstances": 5,
    "allowedIn": ["page", "post"]
  }
}
```

## 3. PHP/JS Handler Requirements

### PHP Handler
- Must extend `BaseBlockHandler`
- Required methods:
  - `renderEdit()` - Returns editor HTML
  - `renderPreview()` - Returns frontend HTML
  - `serialize()` - Prepares data for storage
  - `deserialize()` - Restores data from storage
- Optional methods:
  - `validate()` - Data validation
  - `onSave()` - Post-save hooks

### JavaScript Handler
- Must register with `BlockManager`
- Required components:
  - EditComponent - React/VanillaJS editor UI
  - PreviewComponent - Frontend rendering
- Must implement:
  - Data binding
  - Event handling
  - Validation

## 4. Sandbox Security Constraints

### PHP Security
- All output must be escaped
- No direct file system access
- Limited PHP functions:
  - No `exec`, `shell_exec`, etc.
  - No file operations outside plugin dir

### JavaScript Security
- Runs in isolated iframe
- Limited DOM access
- CSP restrictions:
  - No inline scripts
  - No eval()
  - Restricted external resources

### Data Validation
- All input validated against schema
- Maximum data size: 64KB per block
- No binary data in JSON

## 5. Editor UI Integration

### Toolbar Integration
1. Add button to block picker
2. Configure drag/drop behavior
3. Set preview thumbnail

### Edit Interface
- Use provided CSS framework
- Follow accessibility guidelines
- Responsive design required

### Preview Rendering
- Must match frontend output
- Support live updates
- Handle error states

## Example Implementation

### PHP Handler
```php
class GalleryBlock extends BaseBlockHandler {
    public function renderEdit(array $data): string {
        return '<div class="gallery-editor">...</div>';
    }
    
    public function validate(array $data): bool {
        return isset($data['images']) && is_array($data['images']);
    }
}
```

### JavaScript Component
```javascript
class GalleryEditor extends HTMLElement {
    connectedCallback() {
        // Editor UI implementation
    }
}
customElements.define('gallery-editor', GalleryEditor);