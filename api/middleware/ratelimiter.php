<?php
/**
 * Rate Limiter Middleware
 * 
 * Implements token bucket algorithm for API rate limiting
 */
class RateLimiter {
    private static $buckets = [];
    private $key;
    private $capacity;
    private $refillRate; // tokens per second
    
    public function __construct(string $key, int $capacity, int $timeWindowSeconds) {
        $this->key = $key;
        $this->capacity = $capacity;
        $this->refillRate = $capacity / $timeWindowSeconds;
        
        if (!isset(self::$buckets[$key])) {
            self::$buckets[$key] = [
                'tokens' => $capacity,
                'last_refill' => microtime(true)
            ];
        }
    }
    
    public function check(): void {
        $bucket = &self::$buckets[$this->key];
        $now = microtime(true);
        $elapsed = $now - $bucket['last_refill'];
        
        // Refill tokens
        $bucket['tokens'] = min(
            $this->capacity,
            $bucket['tokens'] + ($elapsed * $this->refillRate)
        );
        $bucket['last_refill'] = $now;
        
        // Check if request allowed
        if ($bucket['tokens'] < 1) {
            throw new Exception('Rate limit exceeded', 429);
        }
        
        $bucket['tokens'] -= 1;
    }
    
    public function getRemaining(): int {
        return floor(self::$buckets[$this->key]['tokens']);
    }
    
    public static function resetAll(): void {
        self::$buckets = [];
    }
}
