# Theme System Architecture

## Components
1. **ThemeManager.vue** - Admin UI component
   - Manages theme selection/creation
   - Handles metadata editing
   - Version control interface

2. **ThemeBuilder.php** - Backend processor
   - File operations
   - Preview generation
   - Security headers

3. **Theme Structure**
   - /templates/layout.php - Main template
   - /assets/ - CSS/JS resources
   - theme.json - Metadata (missing, needs implementation)

## Data Flow
1. UI (Vue) ↔ REST API ↔ PHP Backend ↔ File System

## API Endpoints
- /api/themes/list - Get all themes
- /api/themes/load - Load specific theme
- /api/themes/save - Save theme changes
- /api/themes/versions - Get version history
- /api/themes/restore - Restore version

## Missing Components
- Export/import endpoints
- theme.json standard
- Package validation