# Builder Engine Current State (2025-06-14)

## Theme System Architecture

### Core Components
1. **Theme Storage Handler**
   - Location: `/themes/core/ThemeStorageHandler.php`
   - Features:
     - Version control (JSON snapshots)
     - Theme configuration management
     - Multi-site support

2. **Template Inheritance**
   - Parent/child theme support
   - Fallback to core templates
   - Path resolution via `Theme.php`

3. **Asset Management**
   - Minification support
   - Versioned asset URLs
   - Site-specific asset paths

### Admin Interface (Vue)
- `ThemeManager.vue`: Main control panel
- `ThemePreview.vue`: Live preview
- `StyleEditor.vue`: CSS preset editor

### API Endpoints
- `/api/themes.php`: Theme CRUD operations
- Version management endpoints
- Configuration endpoints

## Integration Points
1. **Page Builder**
   - JSON config exchange
   - Template selection
   - Style presets

2. **AI Integration**
   - AI-generated content blocks
   - Style suggestions
   - `/api/ai/generate-block` endpoint

3. **Version Control**
   - Database snapshots
   - JSON version history
   - Rollback capability

## Technical Constraints
- PHP 8.1+ compatible
- FTP-deployable
- No CLI dependencies
- Shared hosting friendly

## Open Questions
1. Theme approval workflow implementation
2. Multi-tenant theme isolation
3. Performance optimization for large themes