# AI API Database Optimizations

## Index Optimizations
Added the following indexes to improve query performance:

1. **AI Prompts Table**:
   - `model_name` index - Faster filtering by AI model
   - `usage_count` index - Faster sorting by popularity
   - Full-text index on `prompt_text` - Better search capabilities

2. **Contents Table**:
   - `content_type` index - Faster content type filtering
   - `user_id` index - Faster user content queries
   - Full-text index on `title` and `content` - Improved search

## Query Caching
Implemented Redis-based query caching:
```php
// Cache frequent queries for 1 hour
$prompts = Cache::remember('popular_prompts', 3600, function () {
    return AIPrompt::where('usage_count', '>', 100)
        ->orderBy('usage_count', 'desc')
        ->get();
});
```

## Connection Pooling Recommendations
1. For MySQL:
   - Install and configure ProxySQL for connection pooling
   - Set `max_connections` in MySQL to handle peak loads
   - Configure Laravel Octane for persistent database connections

2. For Redis:
   - Increase `tcp-keepalive` to maintain connections
   - Configure connection pool size in Laravel database config

## Performance Benchmarks
To test optimizations:
```bash
# Run before and after optimizations
php artisan benchmark:ai-queries --iterations=1000
```

Key metrics to track:
- Average query time
- 95th percentile latency
- Throughput (queries/second)

## Rollback Procedures
1. To revert indexes:
```bash
php artisan migrate:rollback --step=1
```

2. To disable caching:
- Remove `Cache::remember` wrappers
- Set `CACHE_DRIVER=array` in .env