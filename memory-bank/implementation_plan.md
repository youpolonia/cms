# CMS Fixes Implementation Plan

## Phase 1: OPCache Clearance and Version Verification
1. **Implementation**:
   - Create `utilities/clear_opcache.php` script
   - Add version check in `config.php`

2. **Verification**:
   - Access `utilities/clear_opcache.php` via browser
   - Check `phpinfo()` for OPCache status
   - Verify `CMS_VERSION` is defined

## Phase 2: CMS_ROOT Standardization
1. **Implementation**:
   - Update all entry points to consistently define CMS_ROOT
   - Create `core/constants.php` for path definitions
   - Replace direct `__DIR__` usage with `CMS_ROOT`

2. **Verification**:
   - Run path consistency checks
   - Verify all require/include paths
   - Check error logs

## Phase 3: Placeholder Routing Replacement
1. **Implementation**:
   - Identify all placeholder routes
   - Implement proper route handlers
   - Update `public/index.php`
   - Add route caching

2. **Verification**:
   - Test all previously placeholder routes
   - Verify route caching
   - Check for 404 errors

## Phase 4: Router Dispatch Re-enablement
1. **Implementation**:
   - Remove router bypass code
   - Enable full router in `bootstrap.php`
   - Add router debug mode

2. **Verification**:
   - Test all endpoints
   - Verify router debug output
   - Check performance metrics

## Documentation
- Update `memory-bank/implementation_plan.md` with results
- Add decisions to `memory-bank/decisionLog.md`