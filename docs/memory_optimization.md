# Memory Optimization Guide

## Overview
The system implements several memory optimization techniques to handle large model loading and processing efficiently.

## Key Features
- Chunked loading of large models
- Memory usage monitoring via WorkerMonitoringService
- Automatic cleanup of temporary buffers
- Memory usage limits enforcement

## Configuration
Configure memory settings in `config/heartbeat.php`:
```php
'memory' => [
    'max_usage' => 512, // MB
    'chunk_size' => 1024, // KB per chunk
    'cleanup_interval' => 60 // seconds
]
```

## Usage Examples
1. Monitoring current memory usage:
```php
$monitor = new WorkerMonitoringService();
$usage = $monitor->getCurrentMemoryUsage();
```

2. Loading models with chunked approach:
```php
$loader = new ModelLoader();
$loader->setChunkSize(2048); // 2MB chunks
$model = $loader->load('/path/to/model.bin');
```

## Best Practices
- Always load large models using chunked approach
- Monitor memory usage during processing
- Set appropriate max_usage based on server resources
- Test with `/test_models` before production use