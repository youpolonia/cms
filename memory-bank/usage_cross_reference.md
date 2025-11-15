# File Usage Cross-Reference Report

## Middleware Files
### Actively Used:
- TenantIsolation.php (used in 8+ federation/API files)
- RateLimiter.php (used in auth and API endpoints)
- AdminAuth.php (used in admin routes and controllers)

### Unused:
- AnalyticsTrackingMiddleware.php
- CsrfMiddleware.php  
- FileAccessMiddleware.php
- PermissionMiddleware.php
- SecurityHeadersMiddleware.php
- ValidationMiddleware.php

## Helpers Files
- version_helpers.php:
  - Marked as [KEEP] in helpers_analysis.md
  - No references found in codebase
  - Consider removing if functionality not needed

## Handlers Files
### Actively Used:
- ContentFileHandler.php (used in content management)

### Unused:
- AbstractBlockHandler.php
- BlockHandler.php
- BlockRegistry.php  
- TextBlockHandler.php
- ImageBlockHandler.php
- VideoBlockHandler.php

## Recommendations
1. Remove unused middleware files after verifying no runtime loading
2. Remove version_helpers.php if functionality not used
3. Either remove unused handlers or implement proper usage tracking
4. Consider consolidating middleware usage patterns