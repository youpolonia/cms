# {{title}} API Reference

## Base URL
`{{baseUrl}}`

## Authentication
Describe authentication method (e.g., API keys, OAuth)

## Endpoints

### {{endpointName}}
`{{httpMethod}} {{endpointPath}}`

**Description**  
{{endpointDescription}}

**Parameters**
| Name | Type | Required | Description |
|------|------|----------|-------------|
| {{param1}} | {{type}} | {{yes/no}} | {{description}} |

**Request Example**
```json
{{requestExample}}
```

**Response Example**
```json
{{responseExample}}
```

**Status Codes**
| Code | Description |
|------|-------------|
| 200 | Success |
| 400 | Bad request |
| 401 | Unauthorized |

## Error Handling
```json
{
  "error": {
    "code": "error_code",
    "message": "Human-readable description"
  }
}
```

## Rate Limits
- {{limit}} requests per {{timePeriod}}
- Describe throttling behavior