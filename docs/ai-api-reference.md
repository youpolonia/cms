# AI API Reference

## Base URL
`/api/ai`

## Endpoints

### POST `/`
Generate AI content

**Request:**
```json
{
  "prompt": "string (required)",
  "model": "string (optional, default: gpt-3.5-turbo)"
}
```

**Response:**
```json
{
  "success": "boolean",
  "data": "object (AI response)",
  "error": "string (if success=false)"
}
```

**Status Codes:**
- 200: Success
- 400: Bad request (missing prompt)
- 405: Method not allowed
- 429: Rate limit exceeded

## Rate Limits
- 5 requests per minute
- Responses are cached for 1 hour

## Available Models
- gpt-3.5-turbo
- gpt-4
- gpt-4-turbo