# API Security Requirements

## Authentication
1. **JWT Tokens Required**
   - All API endpoints require valid JWT in `Authorization` header
   - Token format: `Bearer {token}`
   - Token expiration: 1 hour
   - Refresh tokens available at `/auth/refresh`

2. **Admin Endpoints**
   - Require `admin` role in JWT claims
   - Verified by [`middleware/AdminAuthMiddleware.php`](middleware/AdminAuthMiddleware.php)

## Rate Limiting
1. **Global Limits**
   - 100 requests/minute per IP
   - Enforced by [`middleware/APIRateLimiter.php`](middleware/APIRateLimiter.php)

2. **Endpoint-Specific Limits**
   - AI endpoints: 30 requests/minute
   - File uploads: 10 requests/minute
   - Configuration changes: 5 requests/minute

## Input/Output Security
1. **Request Validation**
   - All input sanitized via [`app/Services/Security.php`](app/Services/Security.php)
   - Required for:
     - Form data
     - JSON payloads
     - Query parameters

2. **Response Filtering**
   - All output filtered for XSS/HTML/JS
   - Location: [`app/Services/ResponseFilter.php`](app/Services/ResponseFilter.php)

## Best Practices
1. **Secure Development**
   - Use prepared statements for all database queries
   - Validate all user input before processing
   - Implement CSRF protection for web forms

2. **Error Handling**
   - Never expose stack traces
   - Generic error messages for production
   - Detailed logging in [`logs/security.log`](logs/security.log)

## Testing Requirements
1. **Security Tests**
   - Run test suite from [`memory-bank/test_plan.md`](memory-bank/test_plan.md)
   - Verify all security features before production deployment

## Compliance
1. **Data Protection**
   - GDPR compliant data handling
   - CCPA compliance for California users
   - HIPAA compliance for health data