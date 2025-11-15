# API v1 Documentation

## Base URL
`/api/v1`

## Authentication
Bearer token in `Authorization` header

## Endpoints

### Content Management
- `GET /content` - List all content
- `POST /content` - Create new content
- `GET /content/{id}` - Get specific content
- `PUT /content/{id}` - Update content
- `DELETE /content/{id}` - Delete content

### Users
- `GET /users` - List users (admin only)
- `POST /users/login` - Authenticate user
- `POST /users/register` - Register new user

### Settings
- `GET /settings` - Get system settings
- `PUT /settings` - Update system settings

### Plugins
- `GET /plugins` - List installed plugins
- `POST /plugins` - Install new plugin
- `DELETE /plugins/{id}` - Uninstall plugin

## Response Format
All responses follow this structure:
```json
{
  "success": boolean,
  "data": mixed,
  "error": null|string,
  "meta": {
    "timestamp": string,
    "version": "v1"
  }
}
```

## Error Codes
- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 500 Server Error