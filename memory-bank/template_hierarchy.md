# CMS Template Hierarchy Analysis

## Core Components
1. **Base Templates** (`/templates/base.php`)
2. **Layout Templates** (`/templates/layout.php`)
3. **Content Templates** (`/content/templates/`)
4. **Field Templates** (`/templates/fields/`)

## Inheritance Flow
```
Base Template (base.php)
  ↑
Layout Template (layout.php)
  ↑
Content Template (e.g. BlogPostTemplate)
  ↑
Field Templates (fields/*)
```

## Resolution Order
1. Tenant-specific override (`/storage/sites/{tenant_id}/templates/`)
2. Theme override (`/assets/themes/{theme}/templates/`)
3. Default location (`/templates/`)

## Key Relationships
- [`View.php`](includes/Core/View.php) handles basic rendering
- [`TemplateInheritance.php`](includes/Theme/TemplateInheritance.php) manages hierarchy
- [`Theme.php`](includes/theme/Theme.php) resolves paths

## Visualization
```
[Base Template]
    ↑
[Layout Template]
    ↑
[Content Type Template] → [Field Templates]
    ↑
[Tenant Overrides]
```

## Recommendations
1. Document inheritance rules in `/docs/template_guide.md`
2. Add visual hierarchy diagram
3. Implement template dependency tracking