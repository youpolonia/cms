# JWT Authentication Implementation for Worker Services

## Overview
This document describes the JWT-based authentication system implemented in [`auth/Middleware/WorkerAuthenticate.php`](auth/Middleware/WorkerAuthenticate.php) for worker heartbeat monitoring and API access.

## Requirements

### Security Requirements
1. **Token Validation**:
   - Must validate JWT signature using HMAC-SHA256
   - Must verify token expiration (default 1 hour)
   - Must validate token structure (3-part JWT format)

2. **Worker Credentials**:
   - Worker ID must be in UUID v4 format
   - Secrets must be stored as password hashes in database
   - Active worker status must be verified

3. **Token Refresh**:
   - Tokens should auto-refresh when expiration is within 10 minutes
   - New tokens must maintain same security level as original

### Implementation Requirements
1. **Dependencies**:
   - Requires database connection for worker validation
   - Uses environment variable `WORKER_JWT_SECRET` for signing key
   - Configurable token lifetime via `WORKER_JWT_LIFETIME` constant

2. **Error Handling**:
   - Must handle invalid/expired tokens
   - Must validate worker credentials
   - Must handle database connection failures

## Implementation Details

### Token Generation Flow
1. Worker provides credentials (worker ID + secret)
2. System validates credentials against database
3. System generates JWT with:
   - Issued at timestamp (`iat`)
   - Expiration timestamp (`exp`)
   - Worker ID (`worker_id`)
   - Secret (`secret`)

```php
protected function generateJwt(string $workerId, string $secret): string {
    $issuedAt = time();
    $expiresAt = $issuedAt + $this->tokenLifetime;
    
    $payload = [
        'iat' => $issuedAt,
        'exp' => $expiresAt,
        'worker_id' => $workerId,
        'secret' => $secret
    ];
    
    // ... JWT construction logic ...
}
```

### Token Validation Flow
1. Extract JWT from request
2. Verify token structure (3 parts)
3. Validate signature using HMAC-SHA256
4. Decode and validate payload
5. Check expiration time
6. Verify worker credentials against database

```php
protected function validateJwt(string $token): array {
    // Verify token structure
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        throw new \RuntimeException('Invalid JWT format');
    }
    
    // Validate signature
    $signature = hash_hmac('sha256', "$parts[0].$parts[1]", $this->jwtSecret, true);
    if (!hash_equals($signature, base64_decode(strtr($parts[2], '-_', '+/'))) {
        throw new \RuntimeException('Invalid JWT signature');
    }
    
    // ... payload validation ...
}
```

### Token Refresh Mechanism
1. Check remaining token lifetime
2. If < 10 minutes remaining:
   - Generate new token with same credentials
   - Return in `X-JWT-Refresh` header
3. Client should use new token for subsequent requests

```php
protected function refreshTokenIfNeeded(array $payload): ?string {
    $timeLeft = $payload['exp'] - time();
    if ($timeLeft < 600) { // 10 minutes
        return $this->generateJwt($payload['worker_id'], $payload['secret']);
    }
    return null;
}
```

## Security Considerations

1. **Secret Management**:
   - JWT secret must be stored in environment variables
   - Never hardcode secrets in source
   - Rotate secrets periodically

2. **Token Security**:
   - Tokens should be transmitted over HTTPS only
   - Client should store tokens securely
   - Tokens contain worker secret - treat as sensitive

3. **Validation Strictness**:
   - All validation failures should reject the request
   - No silent failures allowed
   - Detailed error logging for security events

## Test Coverage

Verified by [`tests/Unit/Middleware/WorkerAuthenticateTest.php`](tests/Unit/Middleware/WorkerAuthenticateTest.php):

- Valid JWT authentication
- Invalid JWT signature detection
- Expired token rejection
- Worker credential validation
- Token refresh logic
- Error handling paths

## Related Documentation
- [Worker Authentication Test Coverage](memory-bank/test_coverage.md)
- [API Security Guidelines](docs/security/API_Security.md)