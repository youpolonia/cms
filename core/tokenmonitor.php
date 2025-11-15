<?php
/**
 * Token Monitor - Implements request throttling and quota management
 */
class TokenMonitor {
    private static $requestQueue = [];
    private static $lastRequestTime = 0;
    private static $backoffDelay = 1000; // Initial delay in ms
    private static $maxDelay = 60000; // Max delay in ms (60s)
    
    // Mode-specific RPM limits
    private static $modeLimits = [
        'code' => 60,
        'architect' => 30,
        'pattern-reader' => 30,
        'db-support' => 30,
        'documents' => 30
    ];

    /**
     * Check if request can proceed based on rate limits
     */
    public static function canProceed(string $mode): bool {
        $currentTime = microtime(true) * 1000;
        $timeSinceLast = $currentTime - self::$lastRequestTime;
        
        // Check if we need to apply backoff
        if ($timeSinceLast < self::$backoffDelay) {
            return false;
        }

        // Get mode-specific limit
        $limit = self::$modeLimits[$mode] ?? 30; // Default to 30 RPM
        
        // Check if we've exceeded the rate limit
        $requestsInLastMinute = array_filter(
            self::$requestQueue,
            fn($t) => $currentTime - $t < 60000
        );
        
        if (count($requestsInLastMinute) >= $limit) {
            // Apply exponential backoff
            self::$backoffDelay = min(
                self::$backoffDelay * 2,
                self::$maxDelay
            );
            return false;
        }

        // Reset backoff if request is allowed
        self::$backoffDelay = 1000;
        return true;
    }

    /**
     * Log a completed request
     */
    public static function logRequest(string $mode): void {
        self::$requestQueue[] = microtime(true) * 1000;
        self::$lastRequestTime = microtime(true) * 1000;
        
        // Keep queue size manageable
        if (count(self::$requestQueue) > 1000) {
            array_shift(self::$requestQueue);
        }
    }

    /**
     * Get current delay in milliseconds
     */
    public static function getCurrentDelay(): int {
        return self::$backoffDelay;
    }

    /**
     * Check if response needs chunking based on token count
     */
    public static function needsChunking(int $tokenCount): bool {
        return $tokenCount > (SystemMonitor::MAX_TOKENS * 0.5); // 50% of max
    }

    /**
     * Split response into chunks that fit within token limits
     */
    public static function chunkResponse(string $response, int $maxChunkSize = null): array {
        $maxChunkSize = $maxChunkSize ?? (int)(SystemMonitor::MAX_TOKENS * 0.75);
        $lines = explode("\n", $response);
        $chunks = [];
        $currentChunk = '';
        $currentSize = 0;

        foreach ($lines as $line) {
            $lineSize = strlen($line) / 4; // Approximate tokens
            if ($currentSize + $lineSize > $maxChunkSize && !empty($currentChunk)) {
                $chunks[] = $currentChunk;
                $currentChunk = '';
                $currentSize = 0;
            }
            $currentChunk .= $line . "\n";
            $currentSize += $lineSize;
        }

        if (!empty($currentChunk)) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    /**
     * Log a chunked response
     */
    public static function logChunkedResponse(string $requestId, int $totalChunks): void {
        file_put_contents(
            __DIR__.'/../logs/chunked_responses.log',
            date('Y-m-d H:i:s')." - $requestId - $totalChunks chunks\n",
            FILE_APPEND
        );
    }
    
    /**
     * Get current token usage
     *
     * @return array Token usage information
     */
    public static function getCurrentUsage(): array {
        $currentTime = microtime(true) * 1000;
        $requestsInLastMinute = array_filter(
            self::$requestQueue,
            fn($t) => $currentTime - $t < 60000
        );
        
        return [
            'requests_last_minute' => count($requestsInLastMinute),
            'backoff_delay' => self::$backoffDelay,
            'last_request_time' => self::$lastRequestTime,
            'time_since_last' => $currentTime - self::$lastRequestTime
        ];
    }
}
