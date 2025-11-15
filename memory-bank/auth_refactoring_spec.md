# Auth System Refactoring Specifications

## Current Architecture Diagram
```
[Authentication Flow]
User → Auth.php → SessionManager.php → CSRFToken.php
```

## Recommended Structure
```
[New Authentication Flow]
User → AuthService → AuthValidator → SessionManager → CSRFToken
```

## Implementation Steps

### 1. Session Management Consolidation
- Move all session logic from Auth.php to SessionManager.php
- Implement SessionManagerInterface
- Update Auth.php to use SessionManager dependency

### 2. CSRF Protection Unification
- Remove CSRF from helpers.php
- Enhance CSRFToken.php with:
  - Token generation
  - Validation
  - Storage
- Add middleware for automatic validation

### 3. AuthValidator Creation
```php
class AuthValidator {
  public static function validateCredentials(string $username, string $password): bool {
    // Validation rules
  }
}
```

### 4. Service Layer Reorganization
```php
class AuthService {
  private SessionManagerInterface $session;
  
  public function __construct(SessionManagerInterface $session) {
    $this->session = $session;
  }
  
  public function login(string $username, string $password): bool {
    // Business logic
  }
}
```

## Migration Plan
1. Phase 1: Create new components alongside old
2. Phase 2: Gradually migrate features
3. Phase 3: Remove legacy code
4. Phase 4: Final testing

## Verification Checklist
- [ ] All tests pass
- [ ] No Laravel patterns remain
- [ ] No broken dependencies
- [ ] Performance benchmarks met