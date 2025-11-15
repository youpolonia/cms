# Authentication API Documentation

## Authentication Flow

1. Request token:
```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

2. Response:
```json
{
  "token": "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...",
  "expires_in": 3600
}
```

3. Use token in subsequent requests:
```
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

## Endpoints

### Login
`POST /api/auth/login`

**Request:**
```json
{
  "email": "user@example.com",
  "password": "password"
}
```

### Logout
`POST /api/auth/logout`

**Response:**
```json
{
  "message": "Successfully logged out"
}
```

### Refresh Token
`POST /api/auth/refresh`

**Response:**
```json
{
  "token": "new_token_here",
  "expires_in": 3600
}
```

## Rate Limits
- Login attempts: 5/minute
- Token refresh: 10/minute
- Authentication required endpoints: 30/minute