# Theme Export/Import Documentation

## Overview
The CMS provides functionality to export and import complete themes including:
- Theme metadata (theme.json)
- Templates
- Assets (CSS, JS, images)
- Configuration settings

## Export Process
1. Navigate to Admin → Themes → Theme Manager
2. Select "Export Theme" from the action menu
3. Choose export options:
   - Include all assets (default: true)
   - Include demo content (default: false)
4. Click "Export" to generate a .zip file

## Import Process
1. Navigate to Admin → Themes → Theme Manager
2. Select "Import Theme" from the action menu
3. Upload theme .zip file
4. System validates:
   - Required theme.json file
   - CMS version compatibility
   - File structure integrity
5. Preview theme details before confirming import

## Theme Package Structure
```
theme-name/
├── theme.json          # Required metadata file
├── templates/          # PHP template files
│   ├── home.php
│   ├── page.php
│   └── post.php
├── assets/             # Static assets
│   ├── css/
│   ├── js/
│   └── images/
└── demo/               # Optional demo content
```

## theme.json Specification
```json
{
  "name": "Theme Name",
  "version": "1.0.0",
  "description": "Theme description",
  "author": "Author Name",
  "license": "MIT",
  "requires": {
    "cms": ">=1.0.0"
  },
  "templates": {
    "home": "templates/home.php",
    "page": "templates/page.php"
  },
  "styles": {
    "main": "assets/css/main.css"
  },
  "scripts": {
    "main": "assets/js/main.js"
  }
}
```

## Validation Rules
- Minimum required fields: name, version, requires.cms
- Version must follow semantic versioning
- Template paths must exist in package
- Asset paths must be relative to theme root
- CMS version requirement must be valid semver range

## Troubleshooting
- **Invalid package**: Ensure theme.json exists and is valid JSON
- **Version conflict**: Check CMS requirements in theme.json
- **Missing templates**: Verify all declared templates exist in package
- **Asset errors**: Confirm asset paths are correct in theme.json