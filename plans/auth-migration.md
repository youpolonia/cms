# Authentication System Migration Plan

## Current System Analysis
- Hybrid Laravel/custom implementation
- Database-backed sessions
- Features: Login, Registration, Password Reset, Email Verification
- Middleware: auth, guest, verified, throttle

## Pure PHP Solution Design

### File Structure
```
/auth
  /Controllers
    AuthController.php
    PasswordController.php
  /Middleware
    Authenticate.php
    Guest.php
  /Services
    AuthService.php
    SessionService.php
  routes.php
```

### Core Components
1. **AuthService**
   - Handles user authentication
   - Password hashing (using PHP's password_hash())
   - CSRF token generation/validation

2. **SessionService**  
   - File-based session storage
   - Implements session regeneration
   - Flash message support

3. **Middleware**
   - Authenticate: Checks logged-in status
   - Guest: Redirects authenticated users
   - Throttle: Rate limiting

## Migration Steps

### Phase 1: Create Core Auth System (3 days)
1. Implement AuthService with:
   - Login/logout
   - Registration
   - Password hashing

2. Create SessionService
3. Develop base middleware

### Phase 2: Replace Controllers (5 days)
1. Convert Laravel controllers to pure PHP
2. Update form handling
3. Implement validation

### Phase 3: Update Routes (2 days)
1. Migrate auth routes to new system
2. Test all endpoints

### Phase 4: Testing (5 days)
1. Unit tests for services
2. Integration tests for flows
3. Security audit

## Affected Files
- app/Http/Controllers/Auth/*
- routes/auth.php  
- config/session.php
- 12 view templates
- 8 test files

## Timeline
- Total: 3 weeks
- Buffer: 1 week