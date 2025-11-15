# Phase 10 API Reference

## Content Testing Engine
### POST /api/v1/content-test
```json
{
  "content_id": "string",
  "test_parameters": {
    "audience": "string",
    "variations": ["string"]
  }
}
```

### GET /api/v1/content-test/results/{test_id}
```json
{
  "test_id": "string",
  "results": {
    "variation_a": 0.75,
    "variation_b": 0.82
  }
}
```

## Analytics API
### GET /api/v1/analytics/aggregates
```json
{
  "tenant_id": "string",
  "date_range": {
    "start": "YYYY-MM-DD",
    "end": "YYYY-MM-DD"
  }
}
```

## Authentication
All endpoints require:
```http
Authorization: Bearer {api_key}
X-Tenant-ID: {tenant_id}