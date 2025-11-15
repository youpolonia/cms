# Asset Standardization Documentation

## Admin Asset Directory Structure
All admin assets are located under `/public/admin/` with this structure:
```
public/admin/
├── assets/
│   ├── css/          # Versioned CSS files (version-*.css)
│   └── js/           # Versioned JavaScript files  
├── css/              # Core admin CSS (admin.css)
└── js/               # Core admin JavaScript
```

## Versioned Asset Standards

### Naming Conventions
1. CSS files: `version-{version}.css` (e.g. `version-1.2.3.css`)
2. JS files: `version-{feature}-{version}.js` (e.g. `version-management-1.0.0.js`)

### Loading Patterns

#### CSS Loading Example
```php
// In admin header template
<link href="/admin/assets/css/version-<?= $currentVersion ?>.css" rel="stylesheet">
<link href="/admin/css/admin.css" rel="stylesheet">
```

#### JavaScript Loading Example
```php
// Before closing </body> tag in admin template
<script src="/admin/js/core.js"></script>
<script src="/admin/assets/js/version-management-<?= $currentVersion ?>.js"></script>
```

## Implementation Guidelines
1. Always use absolute paths with `/admin/` prefix
2. Versioned assets should be loaded after core assets
3. Cache busting should be handled via version numbers in filenames
4. Core admin.css/js should remain unversioned for critical functionality

## Changes Made
1. Moved admin.css to `/public/admin/css/`
2. Updated references in:
   - admin/index.php
   - admin/content.php
3. Organized versioned assets under `/public/admin/assets/`

## Future Updates Needed
- Update remaining admin files (media.php, settings.php)
- Verify all asset references follow new standards
- Document this standard in developer documentation