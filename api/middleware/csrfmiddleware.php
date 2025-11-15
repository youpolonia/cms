<?php
// CSRF Protection Middleware v1.0
// Validates CSRF tokens for all POST/PUT/PATCH/DELETE requests

class CsrfMiddleware {
    private $excludedRoutes = [
        '/api/webhook',
        '/api/status'
    ];

    public function __invoke($request, $response, $next) {
        // Skip CSRF check for excluded routes
        if (in_array($request->getUri()->getPath(), $this->excludedRoutes)) {
            return $next($request, $response);
        }

        // Only validate for state-changing methods
        if (in_array($request->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $token = $request->getHeaderLine('X-CSRF-Token') ?: 
                    ($request->getParsedBody()['csrf_token'] ?? '');

            try {
                SecurityHelper::validateCsrfToken($token);
            } catch (Exception $e) {
                error_log("CSRF violation detected from IP: " . $_SERVER['REMOTE_ADDR']);
                return $response->withStatus(403)
                    ->withHeader('Content-Type', 'application/json')
                    ->write(json_encode([
                        'error' => 'CSRF token validation failed',
                        'code' => 403
                    ]));
            }
        }

        // Add CSRF token to response for next request
        $response = $response->withHeader('X-CSRF-Token', SecurityHelper::generateCsrfToken());
        
        return $next($request, $response);
    }
}
