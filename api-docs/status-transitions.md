# Status Transitions API Documentation

## Overview
The Status Transitions API manages content state changes through predefined workflows. It handles transitions between states like draft, review, approved, and published.

## Authentication
- Requires valid API token with `workflow_manager` role
- Token must be passed in `Authorization` header

## Rate Limits
- 60 requests per minute per tenant
- Responses include `X-RateLimit-Limit` and `X-RateLimit-Remaining` headers

## Endpoints

### POST /status-transitions/transition
Initiate a state transition for content.

**Request:**
```json
{
  "content_id": "string (required)",
  "from_state": "string (current state)",
  "to_state": "string (target state)",
  "comment": "optional transition notes"
}
```

**Response:**
```json
{
  "transition_id": "string",
  "status": "pending|approved|rejected",
  "next_actions": ["array", "of", "possible", "actions"]
}
```

### GET /status-transitions/history
Retrieve transition history for content.

**Query Parameters:**
- `content_id` (required) - Content to query
- `limit` (optional) - Max entries to return (default: 50)

**Response:**
```json
[
  {
    "transition_id": "string",
    "from_state": "string",
    "to_state": "string",
    "timestamp": "ISO8601 datetime",
    "initiator": "user_id"
  }
]
```

### POST /status-transitions/approve
Approve a pending transition.

**Request:**
```json
{
  "transition_id": "string (required)",
  "approver_notes": "optional comments"
}
```

**Response:**
```json
{
  "status": "approved",
  "effective_at": "ISO8601 datetime"
}
```

### Error Responses
```json
{
  "error": "invalid_transition",
  "message": "Transition from [current] to [target] is not allowed",
  "allowed_transitions": ["array", "of", "valid", "targets"]
}