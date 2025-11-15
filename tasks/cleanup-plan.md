# CMS Cleanup Plan

## Deprecated/Orphaned Files

### Admin Views
- [ ] `admin/diagnostic-dashboard.php` (orphaned) - Remove or repurpose
- [ ] `admin/users/index_view.php` (duplicate) - Remove
- [ ] `admin/blog-*.php` legacy views - Remove all

### Analytics
- [ ] `admin/analytics/old-dashboard.php` - No backend connections
- [ ] `admin/analytics/legacy-reports.php` - Deprecated API

## Cleanup Actions
1. Audit all listed files for dependencies
2. Create backups before removal
3. Update any references to moved/renamed files
4. Verify functionality after cleanup

## Status
- Last audit: 2025-06-15
- Next review: 2025-07-15