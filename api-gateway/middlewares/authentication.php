<?php
/**
 * Authentication Middleware
 *
 * Validates JWT tokens and sets user context
 */
class Authentication {
    public function __invoke(array $request, PDO $pdo, callable $next): array {
        $token = $this->getBearerToken($request['headers']['Authorization'] ?? null);
        
        if (!$token || !($payload = JWT::validate($token))) {
            return [
                'status' => 401,
                'body' => ['error' => 'Invalid authentication token']
            ];
        }

        $request['user_id'] = $payload['user_id'];
        return $next($request);
    }

    private function getBearerToken(?string $header): ?string {
        if (!$header || !preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return null;
        }
        return $matches[1];
    }
}
