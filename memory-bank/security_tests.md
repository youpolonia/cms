# Security Test Cases

## Session Security
1. **Session Fixation**
- [ ] Verify SecureSession::regenerateId() called after login
- [ ] Test session ID changes post-authentication

2. **Session Hijacking**
- [ ] Add IP/browser fingerprint validation
- [ ] Implement idle timeout (current: 24min)

3. **Cookie Security**
- [x] HttpOnly flag set
- [x] Secure flag set when HTTPS
- [x] SameSite=Strict

## CSRF Protection
1. **Token Validation**
- [x] CSRFToken::validate() uses hash_equals()
- [ ] Add per-form token validation

## Tenant Isolation
1. **Validation**
- [ ] Add IP validation to TenantIsolation
- [ ] Test invalid tenant ID rejection