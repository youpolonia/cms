# Builder Engine Specifications

## Core Components
- **Page Builder**: Handles content structure and layout
- **Theme Builder**: Manages visual styling and presets
- **AI Block**: Generates and suggests content
- **Version Control**: Tracks content changes

## Integration Points

### Page ↔ Theme Builder
- Theme styles applied via CSS classes
- Theme presets stored in `/themes/core/`
- JSON configuration format

### AI Block ↔ Page Builder
- Content generated via `/api/ai/generate-block`
- Suggestions displayed in Vue component
- Version created after content acceptance

### Version Control
- Database storage via `ContentVersionModel`
- JSON snapshots for themes
- Autosave support

## Technical Requirements
- PHP 8.1+ compatible
- FTP-deployable (no CLI dependencies)
- Static methods only
- JSON-based configuration