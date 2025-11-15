<?php
/**
 * Rate Limiting Middleware
 * Protects API endpoints from excessive requests
 */

class RateLimitMiddleware {
    private $limits = [
        'default' => 100, // requests per minute
        'auth' => 30,
        'admin' => 200
    ];

    public function process($request, $response) {
        $clientKey = $request->headers['X-API-KEY'] ?? $request->ip();
        $routeType = $this->getRouteType($request);
        $limit = $this->limits[$routeType] ?? $this->limits['default'];

        $cacheKey = "rate_limit:$clientKey:$routeType";
        $current = $this->getCurrentCount($cacheKey);

        if ($current >= $limit) {
            $response->setStatusCode(429);
            $response->setBody(json_encode([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded'
            ]));
            $response->send();
            exit;
        }

        $this->incrementCount($cacheKey);
    }

    private function getRouteType($request) {
        if (strpos($request->path, '/api/auth') === 0) return 'auth';
        if (strpos($request->path, '/api/admin') === 0) return 'admin';
        return 'default';
    }

    private function getCurrentCount($key) {
        // Implement your preferred caching mechanism here
        // Example using file-based cache:
        $cacheFile = __DIR__ . "/../../storage/cache/$key.cache";
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            if (time() - $data['timestamp'] < 60) {
                return $data['count'];
            }
        }
        return 0;
    }

    private function incrementCount($key) {
        $cacheFile = __DIR__ . "/../../storage/cache/$key.cache";
        $count = $this->getCurrentCount($key) + 1;
        file_put_contents($cacheFile, json_encode([
            'count' => $count,
            'timestamp' => time()
        ]));
    }
}
