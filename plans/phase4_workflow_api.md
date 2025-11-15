# Phase 4: Workflow API Specification

## API Endpoints
1. `POST /api/workflows` - Create new workflow
2. `GET /api/workflows/{id}` - Get workflow details  
3. `PUT /api/workflows/{id}` - Update workflow
4. `DELETE /api/workflows/{id}` - Remove workflow
5. `POST /api/workflows/{id}/execute` - Trigger execution

## Request/Response Formats
```json
// Create Request
{
  "name": "string",
  "description": "string",
  "steps": [
    {
      "type": "ai|webhook|condition",
      "config": {}
    }
  ]
}

// Success Response
{
  "id": "uuid",
  "status": "active|inactive",
  "created_at": "timestamp"
}
```

## Error Handling
- Standard HTTP status codes
- Consistent error format:
```json
{
  "error": {
    "code": "string",
    "message": "string",
    "details": []
  }
}
```

## Security
- JWT authentication required
- Role-based access control
- Input validation/sanitization

## Integration Points
1. AI Service Connectors
2. n8n Webhook Support
3. CMS Event System