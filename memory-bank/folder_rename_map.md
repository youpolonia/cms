# Folder Rename Map

## Before → After
- includes/Core/ → includes/core/
- Http/ → http/
- app/ → application/
- resources/ → assets/

## Verification Summary
- includes/Core/: 87 files
- Http/: 2 files
- app/: 50 files
- resources/: 42 files

## Special Notes
1. Case-sensitive rename for includes/Core/ → includes/core/
2. No changes needed for:
   - config_core/ (62 files)
   - routes_custom/ (26 files)
   - admin/Controllers/ (empty)
3. Orphaned files to remove:
   - views/analytics/dashboard.blade.php.orphaned