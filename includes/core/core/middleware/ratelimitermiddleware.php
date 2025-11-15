<?php
/**
 * Rate Limiter Middleware
 * Implements API rate limiting per client
 */

class RateLimiterMiddleware {
    private $limit;
    private $window;
    private $storagePath;

    public function __construct($limit = 100, $window = 3600) {
        $this->limit = $limit;
        $this->window = $window;
        $this->storagePath = __DIR__ . '/../../storage/rate_limits/';
        
        if (!file_exists($this->storagePath)) {
            mkdir($this->storagePath, 0755, true);
        }
    }

    public function process($request, $response) {
        $clientKey = $this->getClientKey($request);
        $filePath = $this->storagePath . md5($clientKey) . '.json';

        $data = $this->getRateData($filePath);
        
        // Reset if window expired
        if (time() - $data['timestamp'] > $this->window) {
            $data = ['count' => 0, 'timestamp' => time()];
        }

        // Check limit
        if ($data['count'] >= $this->limit) {
            $response->setStatusCode(429);
            $response->setBody(json_encode([
                'error' => 'Rate limit exceeded',
                'retry_after' => $this->window - (time() - $data['timestamp'])
            ]));
            $response->send();
            exit;
        }

        // Increment count
        $data['count']++;
        file_put_contents($filePath, json_encode($data));
    }

    private function getClientKey($request) {
        return $_SERVER['REMOTE_ADDR'] . ($request->headers['Authorization'] ?? '');
    }

    private function getRateData($filePath) {
        if (file_exists($filePath)) {
            return json_decode(file_get_contents($filePath), true);
        }
        return ['count' => 0, 'timestamp' => time()];
    }
}
