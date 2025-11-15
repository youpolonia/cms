<?php
declare(strict_types=1);

namespace Api\Gateway\Middleware;

class ValidationMiddleware
{
    public static function handle(array $request, callable $next): array
    {
        $config = require_once __DIR__ . '/../../config.php';
        
        // Skip validation if disabled
        if (!$config['security']['validation']['enabled']) {
            return $next($request);
        }

        // Check content-type if strict mode enabled
        if ($config['security']['validation']['strict_content_type']) {
            $contentType = $request['headers']['Content-Type'] ?? '';
            if (!str_contains($contentType, 'application/json')) {
                return self::invalidContentTypeResponse();
            }
        }

        // Validate request structure
        if (!isset($request['method'], $request['path'], $request['headers'])) {
            return self::invalidRequestResponse();
        }

        return $next($request);
    }

    private static function invalidContentTypeResponse(): array
    {
        return [
            'status' => 415,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['error' => 'Unsupported Media Type'])
        ];
    }

    private static function invalidRequestResponse(): array
    {
        return [
            'status' => 400,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => json_encode(['error' => 'Bad Request'])
        ];
    }
}
