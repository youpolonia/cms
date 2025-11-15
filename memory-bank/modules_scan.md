# CMS Modules Analysis
## Module Registration Patterns
1. **Array Format** (modules/auth/routes.php)
   - Returns array of routes
   - Converted by module class (AuthModule.php) to Core\Router calls

2. **Direct Router Calls** (routes/workflows.php)
   - Directly calls Core\Router methods

## Module References
- **Auth Module**:
  - Registered via modules/auth/AuthModule.php
  - Routes referenced in modules/auth/routes.php
  - No duplicate auth functionality in plugins/

- **Workflows**:
  - Routes defined in routes/workflows.php
  - Uses WorkflowController

## Orphaned Modules (No References Found)
- ai-content/
- AIAdvisor/ 
- builder-v2/
- BuilderEngine/
- content/
- CPT/
- DocCompiler/
- FlowGenerator/
- MediaGallery/
- plugin-system/
- PluginSystem/
- sample_module/
- SEOToolkit/
- theme-engine/

## Issues Found
- view() helper in modules/auth/routes.php may be Laravel remnant
- Inconsistent module registration patterns
- Multiple modules with no clear references

## Recommendations
1. Standardize module registration pattern
2. Remove view() helper dependency
3. Investigate orphaned modules for removal
4. Document module registration process