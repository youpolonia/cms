# Metric Collection Intervals

## Performance Metrics Collection

### Automatic Collection
- **Every request**: Execution time and memory usage
- **Every 5 minutes**: System-wide metrics (CPU, memory, disk)
- **Every hour**: Tenant-level aggregate metrics

### Manual Collection
```php
// Example manual collection
$benchmark = new BenchmarkService();
$benchmark->setTenantContext('tenant123');
$metrics = $benchmark->measurePerformance(
    fn() => $this->processData(),
    'data_processing'
);
```

## Data Retention
| Metric Type       | Retention Period | Storage Location |
|-------------------|------------------|------------------|
| Request-level     | 7 days           | Redis cache      |
| Tenant aggregates | 30 days          | Database         |
| System-wide       | 90 days          | Time-series DB   |

## Sampling Rates
- High-traffic endpoints: 10% sampling
- Background jobs: 100% sampling
- API endpoints: 25% sampling

## Configuration
Set collection intervals in `config/benchmark.php`:
```php
return [
    'intervals' => [
        'request' => 100, // ms
        'system' => 300, // seconds
        'tenant' => 3600 // seconds
    ]
];