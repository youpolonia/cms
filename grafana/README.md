# Monitoring Setup Documentation

## Performance Monitoring Configuration

### Prerequisites
- Prometheus server running and scraping metrics
- Grafana instance connected to Prometheus datasource
- Alertmanager configured for notifications

### Dashboard Setup
1. Import the performance dashboard JSON:
   - Navigate to Grafana -> Create -> Import
   - Upload `grafana/performance-dashboard.json`
   - Select Prometheus as datasource

2. Configure dashboard variables:
   - Set the `environment` variable to filter by environment

### Metrics Collection
The dashboard expects these metrics to be collected:

#### System Metrics
- `node_cpu_seconds_total`
- `node_memory_MemTotal_bytes`
- `node_memory_MemFree_bytes`
- `node_memory_Buffers_bytes`
- `node_memory_Cached_bytes`

#### Application Metrics
- `http_request_duration_seconds_bucket`
- `http_requests_total`

#### Database Metrics
- `db_query_duration_seconds_bucket` 
- `db_connections_active`
- `db_connections_idle`

#### Caching Metrics
- `cache_hits_total`
- `cache_misses_total`
- `cache_operations_total`

### Alerting Setup
Configure these alert rules in Alertmanager:

```yaml
groups:
- name: performance-alerts
  rules:
  - alert: HighErrorRate
    expr: sum(rate(http_requests_total{status=~"5.."}[5m])) by (route) / sum(rate(http_requests_total[5m])) by (route) > 0.05
    for: 10m
    labels:
      severity: critical
    annotations:
      summary: "High error rate on {{ $labels.route }}"
      description: "Error rate is {{ $value }}%"

  - alert: DatabaseSlowQueries  
    expr: histogram_quantile(0.95, sum(rate(db_query_duration_seconds_bucket[5m])) by (le, query_type) > 1
    for: 5m
    labels:
      severity: warning
    annotations:
      summary: "Slow database queries detected"
      description: "95th percentile query duration is {{ $value }}s"
```

### Performance Baseline Tracking
To establish performance baselines:

1. Run load tests against the system
2. Capture metrics during normal operation
3. Set dashboard annotations for performance thresholds
4. Configure Grafana alerts for deviations from baseline