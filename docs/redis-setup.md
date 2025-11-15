# Redis Server Setup and Monitoring

## Configuration

1. **Environment Variables**:
   ```env
   REDIS_CLIENT=phpredis
   REDIS_HOST=127.0.0.1
   REDIS_PORT=6379
   REDIS_PASSWORD=null
   REDIS_DB=0
   REDIS_CACHE_DB=1
   CACHE_DRIVER=redis
   ```

2. **Laravel Configuration**:
   - Configured in `config/database.php` and `config/cache.php`
   - Uses separate databases for cache (DB 1) and regular data (DB 0)

## Health Checks

A health check endpoint is available at `/health/redis` that returns:
- Redis server status
- Version information
- Memory usage
- Connection count

Example response:
```json
{
  "status": "healthy",
  "details": {
    "redis_version": "6.2.6",
    "uptime_in_seconds": 12345,
    "used_memory_human": "1.23M",
    "connected_clients": 5
  }
}
```

## Monitoring Dashboard

A Grafana dashboard is configured at `grafana/redis-dashboard.json` that monitors:
- Memory usage
- Active connections
- Command throughput
- Uptime
- Cache hit rate

To import:
1. Navigate to Grafana -> Create -> Import
2. Upload the JSON file
3. Select Prometheus as the datasource

## Alerting

Alertmanager is configured with these Redis-specific alerts:

1. **RedisDown** (Critical):
   - Triggered when Redis is unreachable
   - Immediate notification

2. **RedisHighMemory** (Critical):
   - Triggered when memory usage exceeds 90%
   - 30s wait before notification

3. **RedisConnectionsHigh** (Warning):
   - Triggered when connection count spikes
   - Warning notification

4. **RedisCacheHitRateLow** (Warning):
   - Triggered when cache hit rate drops below 80%
   - Warning notification

## Troubleshooting

Common issues and solutions:

1. **Connection refused**:
   - Verify Redis server is running: `redis-cli ping`
   - Check firewall settings

2. **High memory usage**:
   - Review keys with `redis-cli --bigkeys`
   - Consider increasing maxmemory or implementing eviction policies

3. **Slow performance**:
   - Check slowlog with `redis-cli slowlog get`
   - Monitor command latency