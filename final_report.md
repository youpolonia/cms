# Final Security Audit Report

This report summarizes the findings of the security audit.

## Summary of Violations

| Check ID | Check Name | Violations Found |
|---|---|---|
| A | `include`/`include_once` | 2 |
| B | Dynamic Includes | 3 |
| C | Closing Tag (`?>`) | 0 |
| D | `exec` Family Functions | 0 |
| E | Autoloader | 0 |
| F | Database Connection | 0 |
| G | `DEV_MODE` Gate Missing | 41 |
| H | Hygiene | 10 |
| **Total** | | **56** |

## Detailed Violations


### Check A: `include`/`include_once`

- **File:** `templates/recommendations/readme.md`
  - **Line:** 11
  - **Code:** `<?php include_once('templates/recommendations/main.html'); ?>`
- **File:** `admin/js/dashboard.js`
  - **Line:** 29
  - **Code:** `container.innerHTML = '<?php include __DIR__ . '/../views/analytics/widget.php'; ?>';`

### Check B: Dynamic Includes

- **File:** `includes/viewrenderer.php.bak`
  - **Line:** 17
  - **Code:** `include $fullViewPath;`
- **File:** `includes/viewrenderer.php.bak`
  - **Line:** 21
  - **Code:** `include $fullLayoutPath;`
- **File:** `includes/viewrenderer.php.bak`
  - **Line:** 29
  - **Code:** `include $fullViewPath;`

### Check G: `DEV_MODE` Gate Missing

The following files handle `$_GET` or `$_POST` data but do not have a `DEV_MODE` check:

- `public/post.php`
- `public/admin/comments.php`
- `public/admin/login.php`
- `public/admin/menus.php`
- `public/admin/menus_create.php`
- `public/admin/menus_delete.php`
- `public/admin/menus_edit.php`
- `public/admin/themes.php`
- `public/login.php`
- `endpoints/migration_0003.php`
- `endpoints/migration_testing.php`
- `endpoints/test/content_locks.php`
- `endpoints/test/version_comparison.php`
- `endpoints/test/version_history.php`
- `developer-tools/memory-browser/router.php`
- `developer-tools/plugin-scaffold/index.php`
- `admin/test-migrations/run-selected.php`
- `endpoints/analytics.php`
- `endpoints/personalization-api.php`
- `endpoints/search-api.php`
- `endpoints/workflow-api.php`
- `public/api/analytics/tenant.php`
- `public/api/content/restore-version.php`
- `public/api/content/save.php`
- `public/api/content/version-diff.php`
- `public/api/federation/sync.php`
- `public/api/media/upload.php`
- `public/api/notifications/poll.php`
- `public/api/task-runner.php`
- `public/api/v1/ai/history.php`
- `public/api/v1/analytics/track-engagement.php`
- `public/api/v1/analytics/track-view.php`
- `public/api/v1/content/create.php`
- `public/api/v1/content/history.php`
- `public/api/v1/content/relations.php`
- `public/api/v1/content/versions.php`
- `public/api/v1/media/search.php`
- `public/api/v1/users/profile.php`
- `public/api/version/compare.php`
- `public/api/workflows.php`
- `public/api/workflows/run.php`
- `public/templates/components/language_switcher.php`

### Check H: Hygiene

The following files have hygiene violations (e.g., double includes):

- **File:** `admin/admin_users.php`
  - **Line:** 4
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`
- **File:** `admin/users.php`
  - **Line:** 5
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`
- **File:** `admin/content.php`
  - **Line:** 5
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`
- **File:** `admin/settings.php`
  - **Line:** 4
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`
- **File:** `admin/themes.php`
  - **Line:** 4
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`
- **File:** `admin/menus.php`
  - **Line:** 4
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`
- **File:** `admin/plugins.php`
  - **Line:** 4
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`
- **File:** `admin/editor.php`
  - **Line:** 4
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`
- **File:** `admin/login.php`
  - **Line:** 4
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`
- **File:** `admin/logout.php`
  - **Line:** 3
  - **Code:** `require_once __DIR__ . '/../includes/auth.php'; // Double include`