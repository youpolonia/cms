# Case-sensitive config.php references found

## File: admin/testing/migrations/test_0017_webhooks.php
- Line 3: `require_once '../../../config.php';`
- Should be updated to reference `core/Config.php` instead
- Full path: `/var/www/html/cms/admin/testing/migrations/test_0017_webhooks.php`