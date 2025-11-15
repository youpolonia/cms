# Authentication Module Documentation

## Overview
The authentication module provides secure user authentication with:
- Login/logout functionality
- Session management
- CSRF protection
- Rate limiting
- Multi-tenant support

## Core Components

### AuthService (`auth/Services/AuthService.php`)
Main authentication service handling:
- User login/logout
- Password hashing/verification
- CSRF token generation/validation
- Current user management

Key methods:
- `login()` - Authenticates user credentials
- `logout()` - Clears session
- `register()` - Handles new user registration
- `hashPassword()`/`verifyPassword()` - Secure password handling
- `generateCsrfToken()`/`validateCsrfToken()` - CSRF protection

### SessionService (`auth/Services/SessionService.php`)
Secure session management with:
- Multi-site isolation
- Cookie security settings (httponly, secure, samesite)
- Session regeneration
- Flash message handling

Configuration:
- Session path per site
- 1-day lifetime
- Automatic regeneration (10% chance per request)

### Authenticate Middleware (`auth/Middleware/Authenticate.php`)
Request-level authentication:
- Enforces login requirement
- Validates tenant access
- Returns 401/403 responses

### RateLimiter (`auth/RateLimiter.php`)
Brute force protection:
- 5 attempts limit
- 15-minute decay window
- IP-based tracking
- Database storage

## Login Flow (`auth/login.php`)
1. CSRF token validation
2. Rate limit check (429 if exceeded)
3. Credential validation
4. Session creation
5. Redirect to dashboard

Security features:
- Password hashing
- Session regeneration
- Input sanitization
- Error handling

## Configuration

### Required Database Tables
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    tenant_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE rate_limits (
    `key` VARCHAR(255) PRIMARY KEY,
    attempts INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Environment Requirements
- PHP 8.1+
- PDO MySQL extension
- HTTPS recommended for production

## Security Measures
1. **Password Security**
   - BCrypt hashing
   - Verification timing attack protection

2. **Session Security**
   - Regenerated IDs
   - Secure cookie flags
   - HttpOnly and SameSite

3. **CSRF Protection**
   - Per-request tokens
   - One-time use validation
   - 1-hour expiration

4. **Rate Limiting**
   - 5 attempts per 15 minutes
   - IP-based tracking
   - Automatic clearing on success

## Usage Examples

### Login
```php
$authService = new AuthService($db, $session);
if ($authService->login($username, $password, $csrfToken)) {
    // Redirect to dashboard
}
```

### Middleware
```php
$app->add(new Authenticate($sessionService));
```

### Rate Limiting
```php
if ($rateLimiter->tooManyAttempts($key)) {
    http_response_code(429);
}
```

## Troubleshooting
- **Session issues**: Verify session path permissions
- **CSRF failures**: Ensure tokens match and aren't expired
- **Rate limits**: Check `rate_limits` table entries