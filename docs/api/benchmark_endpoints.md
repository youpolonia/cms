# Benchmark API Endpoints

## GET /api/benchmark/metrics
Returns performance metrics for the current tenant.

### Request
```http
GET /api/benchmark/metrics
X-Tenant-Context: tenant123
```

### Response
```json
{
  "average_response_time": 45.2,
  "peak_memory_usage": 12.5,
  "query_count": 8,
  "cache_hit_rate": 0.75,
  "tenant_id": "tenant123",
  "timestamp": "2025-06-05T22:53:45+01:00"
}
```

## GET /api/benchmark/test?action=verify
Verifies benchmark functionality for a tenant.

### Request
```http
GET /api/benchmark/test?action=verify
X-Tenant-Context: tenant123
```

### Response
```json
{
  "status": "verified",
  "metrics_available": true,
  "tenant": "tenant123"
}
```

### Error Codes
| Code | Description |
|------|-------------|
| 400  | Missing action parameter |
| 401  | Invalid tenant context |
| 503  | Metrics service unavailable |