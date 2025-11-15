# Security Coding Standards

## File Loading
- **REQUIRED:** Use `require_once` or `require` exclusively for file inclusion
- **FORBIDDEN:** No `include` or `include_once` (use require variants only)
- **FORBIDDEN:** No autoloaders (`spl_autoload_register`, `__autoload`, Composer autoloader)
- **FORBIDDEN:** No `vendor/autoload.php` references
- **FORBIDDEN:** No dynamic includes with variable paths as first argument
  - ✅ Allowed: `require_once __DIR__ . '/config.php'`
  - ❌ Forbidden: `require_once $path;`
  - ❌ Forbidden: `require_once getConfigPath();`

## System Calls
- **FORBIDDEN:** No system execution functions
  - `system()`, `exec()`, `shell_exec()`, `passthru()`, `popen()`, `proc_open()`
- **FORBIDDEN:** No `php://stdin` usage

## CSRF Protection
- **REQUIRED:** All `<form method="post">` must include `<?= csrf_field(); ?>` immediately after opening tag
- **REQUIRED:** All POST handlers must call `csrf_validate_or_403()` before processing
  ```php
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      require_once __DIR__ . '/../core/csrf.php';
      csrf_validate_or_403();
      // ... process form
  }
  ```

## Test and Debug Endpoints
- **REQUIRED:** All test/debug files in `public/` must have DEV_MODE gate
  ```php
  require_once __DIR__ . '/../config.php';
  if (!defined('DEV_MODE') || DEV_MODE !== true) {
      http_response_code(403);
      exit;
  }
  ```
- **REQUIRED:** Test/debug endpoints must use `/(test|debug)/i` naming pattern

## File Naming and Structure
- **REQUIRED:** Use lowercase with hyphens for directories: `admin-tools/`, `custom-fields/`
- **REQUIRED:** Use PascalCase for class files: `ContentManager.php`, `UserAuth.php`
- **REQUIRED:** Use lowercase with underscores for utility files: `helper_functions.php`, `auth_helpers.php`

## PHP Closing Tags
- **FORBIDDEN:** No closing `?>` tag at end of PHP-only files
- **REQUIRED:** Exactly one trailing newline at EOF
- **ALLOWED:** Closing tags only in mixed PHP/HTML template files

## Logging and Error Handling
- **REQUIRED:** Use `error_log()` for system errors
- **REQUIRED:** Never log sensitive data (passwords, tokens, full credit card numbers)
- **REQUIRED:** Log security events (failed auth, CSRF violations, permission denials)
- **FORBIDDEN:** No `var_dump()`, `print_r()`, or `echo` for debugging in production paths
- **REQUIRED:** Wrap debug output in DEV_MODE checks

## Path and File Access
- **REQUIRED:** Validate all file paths before use
- **REQUIRED:** Use absolute paths with `__DIR__` constant for reliability
- **FORBIDDEN:** No user-controlled paths without whitelist validation
- **REQUIRED:** Check file existence with `file_exists()` before operations

## Configuration and Secrets
- **REQUIRED:** Store secrets in `/config.php` (outside webroot when possible)
- **FORBIDDEN:** No hardcoded credentials in code files
- **REQUIRED:** Use environment-specific config files
- **REQUIRED:** Add `.env`, `config.php`, `*.key` to `.gitignore`

## Session Security
- **REQUIRED:** Always start session before access: `session_start()`
- **REQUIRED:** Check session status before starting: `session_status() === PHP_SESSION_NONE`
- **REQUIRED:** Regenerate session ID on authentication state changes
- **REQUIRED:** Set secure session configuration in production

## Input Validation
- **REQUIRED:** Validate and sanitize all user input
- **REQUIRED:** Use prepared statements for all database queries
- **REQUIRED:** Use `htmlspecialchars()` for output in HTML contexts
- **REQUIRED:** Validate file uploads (type, size, extension)

## Directory Protection
- **REQUIRED:** All sensitive directories must have `.htaccess` deny rules
  ```apache
  Order Deny,Allow
  Deny from all
  ```
- **REQUIRED:** Admin directories require authentication enforcement

## Deployment Standards
- **REQUIRED:** All code must pass `/admin/tools/security_audit.php` with zero issues
- **REQUIRED:** Set `DEV_MODE=false` in production
- **REQUIRED:** Remove or gate all test/debug files before deployment
