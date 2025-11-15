<?php
/**
 * Analytics Tracking Middleware
 * Tracks page views, API requests, and user sessions
 */

class AnalyticsTrackingMiddleware {
    private $pdo;
    private $excludedRoutes = [
        '/api/health',
        '/api/status',
        '/test/'
    ];

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }


    public function handle($request, $next) {
        $response = $next($request);

        // Skip tracking for excluded routes
        if ($this->shouldSkipTracking($request)) {
            return $response;
        }

        try {
            // Track page view for web requests
            if ($this->isWebRequest($request)) {
                $this->trackPageView($request);
            }

            // Track API request
            if ($this->isApiRequest($request)) {
                $this->trackApiRequest($request);
            }

            // Track user session activity
            if ($this->hasUserSession($request)) {
                $this->trackUserActivity($request);
            }
        } catch (Exception $e) {
            $errorDetails = [
                'timestamp' => date('c'),
                'error' => $e->getMessage(),
                'request_path' => $request->getPath(),
                'request_method' => $request->getMethod(),
                'stack_trace' => $e->getTraceAsString()
            ];
            
            file_put_contents(
                __DIR__ . '/../../memory-bank/analytics_errors.log',
                json_encode($errorDetails) . PHP_EOL,
                FILE_APPEND
            );
        }

        return $response;
    }

    private function shouldSkipTracking($request) {
        $path = $request->getPath();
        foreach ($this->excludedRoutes as $route) {
            if (strpos($path, $route) === 0) {
                return true;
            }
        }
        return false;
    }

    private function isWebRequest($request) {
        return $request->getHeader('Accept') === 'text/html';
    }

    private function isApiRequest($request) {
        return strpos($request->getPath(), '/api/') === 0;
    }

    private function hasUserSession($request) {
        return $request->getSession() !== null;
    }

    private function trackPageView($request) {
        $stmt = $this->pdo->prepare("
            INSERT INTO page_views (
                tenant_id, 
                url, 
                referrer, 
                session_id, 
                user_id, 
                ip_address, 
                user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $session = $request->getSession();
        $stmt->execute([
            $request->getTenantId(),
            $request->getPath(),
            $request->getHeader('Referer'),
            $session ? $session->getId() : bin2hex(random_bytes(16)),
            $session ? $session->getUserId() : null,
            $request->getIp(),
            $request->getHeader('User-Agent')
        ]);
    }

    /**
     * Sanitizes JSON request data and handles malformed/invalid input
     */
    private function sanitizeJsonRequest($request) {
        $content = $request->getContent();
        
        if (empty($content)) {
            return [];
        }

        try {
            // Remove invalid Unicode characters
            $cleaned = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '', $content);
            
            $data = json_decode($cleaned, true, 512, JSON_THROW_ON_ERROR);
            
            if (!is_array($data)) {
                return [];
            }
            
            return $data;
        } catch (JsonException $e) {
            $errorDetails = [
                'timestamp' => date('c'),
                'error' => $e->getMessage(),
                'request_path' => $request->getPath(),
                'content_sample' => substr($content, 0, 100)
            ];
            
            file_put_contents(
                __DIR__ . '/../../memory-bank/json_parse_errors.log',
                json_encode($errorDetails) . PHP_EOL,
                FILE_APPEND
            );
            
            return [];
        }
    }

    private function trackApiRequest($request) {
        try {
            $requestData = $this->sanitizeJsonRequest($request);
            
            $stmt = $this->pdo->prepare("
                INSERT INTO user_events (
                    tenant_id,
                    event_type,
                    event_data,
                    user_id,
                    session_id
                ) VALUES (?, ?, ?, ?, ?)
            ");

            $session = $request->getSession();
            $stmt->execute([
                $request->getTenantId(),
                'api_request',
                json_encode([
                    'method' => $request->getMethod(),
                    'path' => $request->getPath(),
                    'status' => http_response_code()
                ]),
                $session ? $session->getUserId() : null,
                $session ? $session->getId() : null
            ]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    private function trackUserActivity($request) {
        $stmt = $this->pdo->prepare("
            INSERT INTO interaction_metrics (
                tenant_id,
                metric_name,
                metric_value,
                time_period,
                dimension,
                dimension_value
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        $session = $request->getSession();
        $stmt->execute([
            $request->getTenantId(),
            'session_activity',
            1,
            'hourly',
            'user_type',
            $session->isAdmin() ? 'admin' : 'regular'
        ]);
    }
}
