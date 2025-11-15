# API Authentication

## Token-Based Authentication
The CMS API uses Laravel Sanctum for token-based authentication:

1. **Obtain Token**:
   ```bash
   POST /api/auth/token
   Content-Type: application/json

   {
     "email": "user@example.com",
     "password": "password",
     "device_name": "api-client"
   }
   ```

2. **Use Token**:
   ```bash
   GET /api/content
   Authorization: Bearer {token}
   ```

## Token Management
- **Expiration**: Tokens never expire by default (configurable)
- **Prefix**: Optional token prefix for security scanning
- **Scopes**: Not currently implemented

## Rate Limiting
- **Default**: 60 requests per minute
- **Throttling**: Returns 429 status code
- **Headers**:
  - `X-RateLimit-Limit`: Total allowed requests
  - `X-RateLimit-Remaining`: Remaining requests
  - `Retry-After`: Seconds until next request

## Stateful Domains
Authenticated cookies are issued for:
- Local development domains
- Production domains
- Configured in `SANCTUM_STATEFUL_DOMAINS`

## Middleware
- Encrypted cookies
- Session authentication
- CSRF protection for stateful domains