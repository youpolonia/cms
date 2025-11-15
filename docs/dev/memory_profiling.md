# Memory Profiling Developer Guide

## Implementation Details
The memory profiling system uses:
- SplFixedArray for efficient memory allocation
- Chunked loading with circular buffers
- Real-time monitoring via WorkerMonitoringService

## Profiling Techniques
1. Basic memory snapshot:
```php
$snapshot = WorkerMonitoringService::takeMemorySnapshot();
```

2. Tracking memory growth:
```php
$monitor = new MemoryGrowthTracker();
$monitor->start();
// Execute operations
$report = $monitor->stop();
```

3. Leak detection:
```php
$leakDetector = new LeakDetector();
$leakDetector->runGarbageCollection();
$potentialLeaks = $leakDetector->analyze();
```

## Debugging Tips
- Use `test_models` for controlled testing
- Enable detailed logging in `config/heartbeat.php`:
```php
'logging' => [
    'level' => 'debug',
    'dump_memory' => true
]
```

## Performance Considerations
- Chunk size affects both memory and I/O performance
- Smaller chunks reduce peak memory but increase I/O
- Recommended chunk sizes:
  - HDD: 1024-2048KB
  - SSD: 512-1024KB