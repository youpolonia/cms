# AI Content Generation API Documentation

## Base URL
`/api/ai-content`

## Authentication
All endpoints require authentication via Sanctum token.

## Endpoints

### Generate Content
`POST /generate`

Generates AI-assisted content based on the provided prompt and template.

**Request Body:**
```json
{
    "prompt": "string (required, max 1000 chars)",
    "template": "string (required, one of: text, html, seo, summary, rewrite, expand)",
    "content_id": "integer (optional, existing content ID for context)",
    "moderate": "boolean (optional, default true)"
}
```

**Response:**
```json
{
    "content": "string (generated content)",
    "moderation": {
        "flagged": "boolean",
        "categories": "object",
        "qualityScore": "number"
    },
    "credits_used": "number"
}
```

### Get Available Templates
`GET /templates`

Returns the list of available content generation templates.

**Response:**
```json
{
    "templates": [
        {
            "value": "text",
            "label": "Plain Text"
        },
        {
            "value": "html",
            "label": "HTML Content"
        },
        // ... other templates
    ]
}
```

## Moderation Safeguards
All generated content goes through automatic moderation that checks for:
- Inappropriate content
- Hate speech
- Self-harm content
- Quality scoring (0-1 scale)

Content failing moderation will return a 422 error.

## Rate Limits
- 10 requests per minute
- 100 requests per hour

## Error Codes
- 401: Unauthorized
- 422: Invalid input or failed moderation
- 429: Rate limit exceeded
- 500: Server error