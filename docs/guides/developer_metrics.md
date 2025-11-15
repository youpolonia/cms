# Developer Guide: Working with Metrics

## Getting Started
```php
// Initialize benchmark service
$benchmark = new BenchmarkService();
$benchmark->setTenantContext('your_tenant_id');
```

## Core Concepts
- **Metrics**: Performance data points (time, memory, queries)
- **Contexts**: Tenant-specific metric isolation
- **Sampling**: Configurable collection rates

## Common Tasks

### Measuring Code Execution
```php
$result = $benchmark->measurePerformance(
    fn() => $this->processOrder($order),
    'order_processing'
);
```

### Analyzing Queries
```php
$analysis = $benchmark->analyzeQuery(
    "SELECT * FROM users WHERE tenant_id = ?"
);
```

### Tracking Cache Performance
```php
$benchmark->trackCacheHitRate('user_profile_123', $cacheHit);
```

## Best Practices
1. Always set tenant context before measurement
2. Use descriptive metric names (e.g., "checkout_processing")
3. Avoid excessive sampling in high-traffic endpoints
4. Review metrics dashboard regularly

## Troubleshooting

### Common Issues
| Symptom | Solution |
|---------|----------|
| Missing tenant data | Verify context is set |
| Inconsistent metrics | Check sampling configuration |
| High memory usage | Reduce metric retention period |

## Integration Examples

### With API Endpoints
```php
class OrderController {
    public function create(Request $request) {
        $metrics = $benchmark->measurePerformance(
            fn() => $this->service->createOrder($request),
            'order_creation'
        );
        
        return response()->json([
            'data' => $order,
            'metrics' => $metrics
        ]);
    }
}
```

### With Background Jobs
```php
class ProcessJob {
    public function handle() {
        $benchmark->setTenantContext($this->job->tenantId);
        
        return $benchmark->measurePerformance(
            fn() => $this->process(),
            'background_job'
        );
    }
}