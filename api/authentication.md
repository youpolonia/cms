# Authentication API

## JWT Authentication
The CMS now uses JSON Web Tokens (JWT) for stateless authentication. This replaces the previous session-based authentication.

### Obtaining a Token
```http
POST /api/auth/token
Content-Type: application/json

{
  "username": "worker@example.com",
  "password": "securepassword"
}
```

Response:
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 3600
}
```

### Using the Token
Include the token in the Authorization header:
```http
Authorization: Bearer {token}
```

### Token Structure
```json
{
  "iss": "cms-api",
  "sub": "worker123",
  "iat": 1716084000,
  "exp": 1716087600,
  "roles": ["editor", "publisher"]
}
```

### Error Responses
```json
{
  "error": {
    "code": "invalid_token",
    "message": "Token verification failed"
  }
}
```

## Backward Compatibility
The system maintains compatibility with legacy session tokens during transition period.

## Security Considerations
- Tokens expire after 1 hour (3600 seconds)
- Use HTTPS for all authentication requests
- Store tokens securely in client applications
- Implement token refresh mechanism if needed

## Migration Notes
- Existing session tokens will be phased out by 2025-07-01
- All new integrations should use JWT exclusively