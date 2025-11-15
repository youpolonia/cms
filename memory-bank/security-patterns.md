# Security Implementation Patterns

## CSRF Protection
1. **Token Generation**:
   - Generate unique per-session tokens using `bin2hex(random_bytes(32))`
   - Store in session: `$_SESSION['csrf_token'] = $token`

2. **Token Validation**:
   ```php
   if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
       throw new SecurityException('CSRF token validation failed');
   }
   ```

## Session Security
1. **Session Configuration**:
   ```php
   session_set_cookie_params([
       'lifetime' => 3600,
       'path' => '/',
       'domain' => $_SERVER['HTTP_HOST'],
       'secure' => true,
       'httponly' => true,
       'samesite' => 'Strict'
   ]);
   ```

2. **Session Validation**:
   - Validate IP address consistency
   - Implement idle timeout (30 minutes)
   - Regenerate ID after login

## File System Security
1. **Directory Permissions**:
   - Cache/logs: 750 (rwxr-x---)
   - Uploads: 750 with strict content validation
   - Config files: 440 (r--r-----)

2. **Development Files**:
   - Remove all test files before deployment
   - Block access to .git directories
   - Scan for .DS_Store files

## Input/Output Security
1. **Input Validation**:
   - Use filter_var() with appropriate filters
   - Whitelist allowed characters where possible

2. **Output Encoding**:
   - Always use htmlspecialchars() for HTML output
   - Use json_encode() for JSON output