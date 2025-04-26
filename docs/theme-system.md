# Theme System Documentation

## Overview
The CMS includes a flexible theme system that allows:
- Multiple theme installation
- Theme activation/deactivation
- Theme preview functionality
- Theme versioning and updates

## Key Components

### 1. Theme Model (app/Models/Theme.php)
- Stores theme metadata and configuration
- Tracks active/preview status

### 2. Theme Service (app/Services/ThemeService.php)
Core functionality includes:
- Theme installation from ZIP packages
- Theme activation/deactivation
- Preview mode management
- Asset management

### 3. Theme Controller (app/Http/Controllers/ThemeController.php)
Handles web routes for:
- Listing available themes
- Theme activation
- Preview management
- Installation/removal

### 4. Theme Structure
Themes require:
- theme.json configuration file
- assets/css/ for stylesheets
- assets/js/ for JavaScript
- views/ for template overrides

### 5. Theme Configuration (theme.json)
Required fields:
```json
{
  "name": "Theme Name",
  "version": "1.0.0",
  "description": "Theme description",
  "author": "Author Name",
  "screenshot": "screenshot.png",
  "styles": ["assets/css/app.css"],
  "scripts": ["assets/js/app.js"]
}
```

## Current Implementation Status
- Theme management UI complete (index/create/show views)
- Theme installation/removal working
- Activation/deactivation working
- Preview mode fully implemented
- Export functionality implemented
- Default theme exists as reference implementation

## Preview Functionality Details
The theme preview system allows:
- Previewing themes without activation
- Persistent preview bar showing active preview
- One-click exit from preview mode
- Preview available from both index and detail views

## Export Functionality Details
The theme export system allows:
- Exporting any installed theme as a ZIP archive
- Preserving all theme files and directory structure
- One-click download from the theme management interface
- Automatic cleanup of temporary files after download

## Next Development Steps

### Theme Versioning System
- Track theme versions in database
- Compare versions during updates
- Show changelog to users
- Support rollback functionality

### Marketplace Integration
- API for browsing remote themes
- One-click installation
- Update notifications
- Rating/review system

### Template Override Enhancements
- Visual diff for template changes
- Conflict resolution UI
- Versioned template overrides
- Better documentation
