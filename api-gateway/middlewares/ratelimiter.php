<?php
/**
 * API Rate Limiter Middleware
 * 
 * Implements global and per-tenant rate limiting
 */
class RateLimiter {
    private static $buckets = [];
    private static $limits = [
        'global' => [
            'requests' => 1000,
            'window' => 60 // seconds
        ],
        'tenant' => [
            'requests' => 100,
            'window' => 60
        ]
    ];

    public function __invoke(array $request, PDO $pdo, callable $next): array {
        $tenantId = $request['tenant_id'] ?? null;
        
        // Check global limit
        if (!$this->checkLimit('global', 'all')) {
            return [
                'status' => 429,
                'body' => ['error' => 'Global rate limit exceeded']
            ];
        }

        // Check tenant limit if applicable
        if ($tenantId && !$this->checkLimit('tenant', $tenantId)) {
            return [
                'status' => 429,
                'body' => ['error' => 'Tenant rate limit exceeded']
            ];
        }

        return $next($request);
    }

    private function checkLimit(string $type, string $key): bool {
        $now = time();
        $limit = self::$limits[$type];
        
        // Initialize bucket if needed
        if (!isset(self::$buckets[$type][$key])) {
            self::$buckets[$type][$key] = [
                'count' => 0,
                'window_start' => $now
            ];
        }

        $bucket = &self::$buckets[$type][$key];

        // Reset window if expired
        if ($now - $bucket['window_start'] > $limit['window']) {
            $bucket['count'] = 0;
            $bucket['window_start'] = $now;
        }

        // Check limit
        if ($bucket['count'] >= $limit['requests']) {
            return false;
        }

        $bucket['count']++;
        return true;
    }
}
