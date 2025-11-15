# API Documentation

## Authentication
All API endpoints require valid authentication tokens.

## CSRF Protection
API endpoints are exempt from CSRF protection by default since they:
- Use token-based authentication
- Don't rely on browser cookies/sessions
- Are typically consumed by non-browser clients

If you need to implement CSRF protection for specific API endpoints:
1. Add the endpoint to `includes/Security/CSRF.php::$protectedEndpoints`
2. Include a valid CSRF token in requests
3. Handle token validation failures appropriately

## Endpoints
[Add your API endpoints documentation here]

## Rate Limiting
All endpoints are subject to rate limiting (100 requests/minute by default).

## Error Handling
Standard HTTP status codes are used:
- 200: Success
- 400: Bad request
- 401: Unauthorized
- 403: Forbidden
- 404: Not found
- 429: Too many requests
- 500: Server error