<?php
/**
 * Lightweight performance monitoring for shared hosting
 * Implements OpenTelemetry-style tracing with tenant context
 */
class PerformanceMonitor {
    private static $spans = [];
    private static $tenantId;

    /**
     * Start a new trace span
     * @param string $name Span name
     * @param string $tenantId Current tenant ID
     */
    public static function startSpan(string $name, string $tenantId): void {
        self::$tenantId = $tenantId;
        self::$spans[] = [
            'name' => $name,
            'tenant' => $tenantId,
            'start' => microtime(true),
            'end' => null,
            'tags' => []
        ];
    }

    /**
     * End current span
     */
    public static function endSpan(): void {
        if (!empty(self::$spans)) {
            $last = count(self::$spans) - 1;
            self::$spans[$last]['end'] = microtime(true);
        }
    }

    /**
     * Add tag to current span
     * @param string $key
     * @param mixed $value
     */
    public static function addTag(string $key, $value): void {
        if (!empty(self::$spans)) {
            $last = count(self::$spans) - 1;
            self::$spans[$last]['tags'][$key] = $value;
        }
    }

    /**
     * Get all spans for current request
     * @return array
     */
    public static function getSpans(): array {
        return self::$spans;
    }

    /**
     * Log spans to storage
     */
    public static function flush(): void {
        if (empty(self::$spans)) return;

        $logEntry = [
            'timestamp' => time(),
            'tenant' => self::$tenantId,
            'spans' => self::$spans
        ];

        // Store in rotating daily files
        $logFile = __DIR__ . '/../logs/perf-' . date('Y-m-d') . '.log';
        file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
        
        self::$spans = [];
    }
}
