# Widget System Architecture Analysis

## Current Findings

1. **Widget Implementation**:
   - Located in `admin/components/widgets/`
   - PHP classes with static render methods
   - Uses output buffering for HTML generation
   - Example: RecentContentWidget.php shows inline HTML pattern

2. **Theme Integration**:
   - `render_theme_view()` function found in `includes/theme_renderer.php`
   - Expected layout.php in themes but missing from default theme
   - Found public theme layout in `themes/default_public/layout.php`

3. **Key Observations**:
   - Widgets currently render HTML directly in PHP classes
   - No clear template separation for widget content
   - Theme system expects layout.php but missing in admin themes

## Recommended Improvements

1. Standardize widget template locations:
   - Create `widgets/` directory in each theme
   - Move HTML from PHP classes to template files

2. Implement widget view rendering:
   - Extend `render_theme_view()` to support widget templates
   - Add widget content injection points in theme layouts

3. Documentation needed:
   - Widget development guidelines
   - Theme integration standards