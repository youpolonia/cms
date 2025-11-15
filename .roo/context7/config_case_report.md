# Case-Sensitive Include/Require Report

## Found Issues

### File: admin/testing/migrations/test_0017_webhooks.php
- Line 3: `require_once '../../../config.php';`
- Issue: Uses lowercase `config.php` instead of proper case `Config.php`
- Recommended fix: Update to `require_once '../../../core/Config.php';`

## Search Parameters
- Regex used: `(include|require)(_once)?\s*['"].*config\.php['"]`
- Scope: Entire codebase (*.php files)
- Search timestamp: 2025-06-12 21:19:45 UTC+1

## Notes for Debug Agent
1. Only one instance found in test migration file
2. Verify if this is an active test file or legacy code
3. Check if relative path needs adjustment when changing to core/Config.php