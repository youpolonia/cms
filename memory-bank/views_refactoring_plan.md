# Views Layer Refactoring Plan

## 1. Header/Footer Standardization
- Standardize on `admin_header.php`/`admin_footer.php` naming
- Consolidate security checks into header.php only
- Update all includes to use `__DIR__` consistently
- Remove duplicate security checks from views

## 2. Layout Wrapper Implementation
- Complete `admin/views/layout.php` with:
  - `render_theme_view()` function
  - Standard content sections
  - Default header/footer includes
- Update existing views to use layout system

## 3. Deprecated View Removal
- Remove confirmed unused files:
  - memory_dashboard.php
  - version_history.css
  - calendar/connection.php
  - content_types/preview.php
  - notifications/*.js files
  - workers/permissions.php.bak

## 4. Asset Reference Updates
- Consolidate asset paths to `/assets/` prefix
- Create subdirectories:
  - `/assets/admin/`
  - `/assets/public/`
- Expand `templates/partials/assets.php` approach
- Audit external dependencies (Font Awesome, Chart.js, etc.)

## 5. Consistent HTML Structure
- Implement standard HTML5 template
- Add required meta tags
- Standardize on semantic HTML
- Ensure proper document structure

## Implementation Phases

### Phase 1: Cleanup (1-2 days)
- Remove deprecated files
- Backup current views
- Document current state

### Phase 2: Standardization (3-5 days)
- Rename header/footer files
- Update include paths
- Consolidate security checks

### Phase 3: Layout System (5-7 days)
- Implement layout.php
- Update views to use layout
- Test all admin views

### Phase 4: Asset Refactoring (7-10 days)
- Move assets to new structure
- Update references
- Implement asset manifest

## Priority Order
1. Remove deprecated files
2. Standardize header/footer
3. Implement layout system
4. Refactor asset paths
5. Update HTML structure