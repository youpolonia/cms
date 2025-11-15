# API Gateway Documentation

## Service Mappings

### AI Service
- **Base URL**: `https://ai-service.internal/api/v1`
- **Authentication**: JWT with `ai.generate` scope

### Endpoints

#### POST /api/ai/generate
Generates AI content based on input prompt

**Request:**
```json
{
  "prompt": "string",
  "max_tokens": 100,
  "temperature": 0.7
}
```

**Response:**
```json
{
  "content": "string",
  "tokens_used": 42,
  "finish_reason": "stop"
}
```

**Authentication:**
- Type: JWT
- Required: true
- Scopes: `ai.generate`

**Timeout**: 30 seconds  
**Retry Attempts**: 2

## Authentication
All endpoints require JWT authentication with:
- Secret from `JWT_SECRET` env variable
- Issuer matching `APP_URL`
- Audience: `api-gateway`

Example JWT header:
```json
{
  "Authorization": "Bearer {token}"
}