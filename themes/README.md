# Theme Development Guide

## Theme Structure

A theme must contain the following files:

```
theme-name/
├── theme.json       # Theme configuration
├── screenshot.png   # Theme preview (1200x900 recommended)
└── assets/          # CSS, JS, images etc.
```

## Configuration Format

The `theme.json` file must contain:

```json
{
  "meta": {
    "name": "Theme Name",
    "version": "1.0.0",
    "author": "Your Name",
    "description": "Theme description"
  },
  "templates": {
    "home": "templates/home.php",
    "page": "templates/page.php"
  },
  "styles": {
    "primary": "#3498db",
    "secondary": "#2ecc71"
  }
}
```

## Required Fields

- `meta.name`: Theme display name
- `meta.version`: Semantic version (e.g. 1.0.0)
- `templates.home`: Path to home template
- `templates.page`: Path to default page template

## Best Practices

1. Prefix all CSS classes with `theme-` to avoid conflicts
2. Use relative paths for assets (`/themes/theme-name/assets/...`)
3. Include a screenshot that accurately represents the theme
4. Document any custom template variables in a README.md

## Example Theme

See the `example-theme` directory for a complete reference implementation.

## Exporting Themes

Themes can be exported from the admin interface as JSON files containing all configuration and assets.