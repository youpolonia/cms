<?php
/**
 * Rate Limiting Middleware
 * 
 * Enforces request rate limits per IP address
 */
class RateLimiting {
    private $redis;
    private $limit;
    private $window;

    public function __construct(Redis $redis, int $limit, int $window) {
        $this->redis = $redis;
        $this->limit = $limit;
        $this->window = $window;
    }

    public function __invoke(array $request, callable $next): array {
        $ip = $request['headers']['X-Forwarded-For'] ?? $_SERVER['REMOTE_ADDR'];
        $key = "rate_limit:$ip";

        $current = $this->redis->get($key);
        if ($current === false) {
            $this->redis->setex($key, $this->window, 1);
            return $next($request);
        }

        if ($current >= $this->limit) {
            return [
                'status' => 429,
                'body' => ['error' => 'Too many requests']
            ];
        }

        $this->redis->incr($key);
        return $next($request);
    }
}
