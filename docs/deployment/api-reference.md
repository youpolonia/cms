# Emergency Endpoint API Reference

## POST /api/system/emergency-endpoint

### Authentication
- Requires valid API credentials via `ApiAuthMiddleware`
- Authentication header format: `Authorization: Bearer {token}`

### Request Format
```json
POST /api/system/emergency-endpoint
Content-Type: application/json

{
  "emergency_type": "string",
  "details": "object"
}
```

### Response Codes
| Status | Description |
|--------|-------------|
| 200 OK | Emergency processed successfully |
| 401 Unauthorized | Invalid/missing authentication |
| 500 Internal Server Error | Server-side error |

### Successful Response
```json
{
  "status": "emergency_processed"
}
```

### Error Handling
```json
{
  "status": "error",
  "message": "Internal server error"
}
```

### Example Usage
```bash
curl -X POST https://example.com/api/system/emergency-endpoint \
  -H "Authorization: Bearer your_token_here" \
  -H "Content-Type: application/json" \
  -d '{"emergency_type": "quota_exceeded", "details": {"service": "gemini-pro"}}'
```

### Logging
All access attempts and errors are logged to:
`storage/logs/system_emergency.log`  
Format:
```
[YYYY-MM-DD HH:MM:SS] Emergency endpoint [message]: {context}
```

---

_For change history and implementation details, see [decision log](memory-bank/decisionLog.md)_