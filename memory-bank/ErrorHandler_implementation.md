# ErrorHandler Implementation Details

## Request Context Logging
1. Add to `logDebug()` method:
```php
$requestContext = [
    'method' => $_SERVER['REQUEST_METHOD'] ?? 'CLI',
    'uri' => $_SERVER['REQUEST_URI'] ?? 'N/A',
    'params' => $_REQUEST
];
$debugInfo['request'] = $requestContext;
```

## Performance Metrics
2. Add tracking to exception handling:
```php
$debugInfo['performance'] = [
    'memory_peak' => memory_get_peak_usage(true),
    'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
];
```

## Error Correlation
3. Implement in `generateErrorId()`:
```php
// Add correlation ID if parent error exists
if (isset($_SESSION['last_error_id'])) {
    $errorData['correlation_id'] = $_SESSION['last_error_id'];
}
$_SESSION['last_error_id'] = $id;
```

## Debug Output
4. Enhance `displayDebugException()`:
```php
echo '<script>
function toggleSection(id) {
    document.getElementById(id).style.display = 
        document.getElementById(id).style.display === "none" ? "block" : "none";
}
</script>';