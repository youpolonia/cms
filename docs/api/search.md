# Search API Documentation

## Overview
The Search API provides content search capabilities with filtering options. It uses file-based caching with 1 hour TTL.

## Base Endpoint
`GET /endpoints/search-api.php`

## Parameters

### Required
- `q` - Search query string

### Optional Filters
- `filters[type]` - Content type (article, page, etc.)
- `filters[archived]` - Archive status (1 for archived, 0 for active)
- `filters[date_from]` - Start date (YYYY-MM-DD)
- `filters[date_to]` - End date (YYYY-MM-DD)

## Examples

### Basic Search
```bash
GET /endpoints/search-api.php?q=example
```

### Filtered Search
```bash
GET /endpoints/search-api.php?q=example&filters[type]=article&filters[archived]=1
```

## Response Format
```json
{
  "results": [
    {
      "id": "content_123",
      "title": "Example Content",
      "type": "article",
      "archived": false,
      "last_modified": "2025-05-15"
    }
  ],
  "meta": {
    "total_results": 1,
    "cache_hit": true
  }
}
```

## Cache Information
- Location: `storage/cache/search/`
- TTL: 1 hour
- Cache key format: `md5(search_query + filters)`

## Error Responses
- `400 Bad Request` - Missing required parameters
- `500 Internal Server Error` - Search service unavailable

## Performance Metrics

### Collected Metrics
- Response time (ms)
- Cache hit ratio (%)
- Result count distribution
- Filter usage statistics

### Accessing Metrics
Metrics are available via:
```bash
GET /endpoints/search-metrics.php
```

### Benchmarks
- Average response time: 120ms (cached), 450ms (uncached)
- 85% cache hit ratio (typical)
- 95th percentile under 800ms

### Optimization Tips
- Use specific filters to reduce result sets
- Leverage cache by reusing common queries
- Avoid overly broad date ranges
- Archive old content to improve index performance