<?php
namespace Middleware;

class AuthMiddleware {
    public static function handle($request, $next) {
        $token = self::getBearerToken($request);
        
        if (!$token) {
            return self::unauthorizedResponse('Missing authentication token');
        }

        if (!self::validateToken($token)) {
            return self::unauthorizedResponse('Invalid token');
        }

        return $next($request);
    }

    protected static function getBearerToken($request) {
        $header = $request->headers['Authorization'] ?? '';
        if (preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private static function validateToken($token) {
        try {
            require_once __DIR__ . '/../auth/jwt.php';
            $jwt = new \Auth\JWT();
            return $jwt->validate($token);
        } catch (\Exception $e) {
            error_log('JWT validation error: ' . $e->getMessage());
            return false;
        }
    }

    protected static function unauthorizedResponse($message) {
        http_response_code(401);
        header('Content-Type: application/json');
        return json_encode([
            'error' => [
                'code' => 'unauthorized',
                'message' => $message
            ]
        ]);
    }
}
