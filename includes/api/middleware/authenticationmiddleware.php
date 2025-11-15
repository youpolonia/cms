<?php
declare(strict_types=1);

namespace CMS\API\Middleware;

use CMS\API\Response;
use Includes\Auth\JWT;
use Includes\Auth\JWTValidationException;
use Includes\Auth\JWTExpiredException;

class AuthenticationMiddleware
{
    private Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function handle(array $request, callable $next): void
    {
        $token = $this->getBearerToken($request['headers'] ?? []);

        if (!$token) {
            $this->response->error('Authorization token required', 401);
        }

        try {
            $payload = $this->validateToken($token);
            $request['user'] = $payload;
            $next($request);
        } catch (\Exception $e) {
            $this->response->error($e->getMessage(), 401);
        }
    }

    private function getBearerToken(array $headers): ?string
    {
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function validateToken(string $token): array
    {
        try {
            return JWT::validateToken($token);
        } catch (JWTExpiredException $e) {
            throw new \Exception('Token expired');
        } catch (JWTValidationException $e) {
            throw new \Exception('Invalid token');
        }
    }
}
