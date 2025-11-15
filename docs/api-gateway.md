# API Gateway Documentation

## Middleware Architecture

The API Gateway uses a middleware pipeline to process requests. Middleware are executed in the order they are defined in the endpoint configuration.

Available middleware:
- `RequestLogger`: Logs all API requests
- `AuthCheck`: Validates authentication tokens
- `ContentValidation`: Validates content creation payloads
- `TenantIdentification`: Validates and tracks tenant context (requires `X-Tenant-Context` header)

## Endpoint Configuration Format

Endpoints are defined in [`api-gateway/config/endpoints.php`](api-gateway/config/endpoints.php) with the following structure:

```php
'endpoint_name' => [
    'method' => 'HTTP_METHOD', // GET, POST, etc.
    'path' => '/api/path',
    'handler' => 'Controller@method',
    'middleware' => ['Middleware1', 'Middleware2'],
    'rate_limit' => [
        'max_requests' => 10,
        'interval' => 60 // seconds
    ]
]
```

## Rate Limiting Specifications

Rate limits are defined per endpoint with:
- `max_requests`: Maximum allowed requests in the interval
- `interval`: Time window in seconds

Example:
```php
'rate_limit' => [
    'max_requests' => 5,
    'interval' => 60
]
```

## Authentication Requirements

Endpoints requiring authentication include the `AuthCheck` middleware. Unauthenticated requests to these endpoints will receive a `401 Unauthorized` response.

## Example Requests/Responses

### Authentication Endpoints

#### Login
```http
POST /auth/login
Content-Type: application/json

{
    "username": "user@example.com",
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

#### Logout
```http
POST /auth/logout
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

Response:
```json
{
    "message": "Successfully logged out"
}
```

### Content Endpoints

#### List Content
```http
GET /content
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

Response:
```json
{
    "data": [
        {
            "id": 1,
            "title": "Example Content",
            "created_at": "2025-06-02T10:00:00Z"
        }
    ]
}
```

#### Create Content
```http
POST /content
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
Content-Type: application/json

{
    "title": "New Content",
    "body": "Content body text"
}
```

Response:
```json
{
    "id": 2,
    "title": "New Content",
    "status": "created"
}
```

## Versioning

The API supports multiple versions (v1, v2) with different endpoint configurations. Version is specified in the URL path (e.g., `/v2/content`).