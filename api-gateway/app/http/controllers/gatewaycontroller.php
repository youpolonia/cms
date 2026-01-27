<?php

namespace App\http\controllers;

class GatewayController
{
    private $serviceMap = [
        'content' => 'http://content-service',
        'versions' => 'http://version-service',
        'search' => 'http://search-service',
        'analytics' => 'http://analytics-service',
        'moderation' => 'http://moderation-service'
    ];

    public function getTenant(string $tenantId): array
    {
        return [
            'status' => 'success',
            'data' => [
                'tenant_id' => $tenantId,
                'active' => true,
                'services' => array_keys($this->serviceMap)
            ]
        ];
    }

    public function route(array $request): array
    {
        if (!isset($request['service']) || !isset($request['path'])) {
            return $this->errorResponse('Invalid request parameters', 400);
        }

        if (!array_key_exists($request['service'], $this->serviceMap)) {
            return $this->errorResponse('Service not found', 404);
        }

        // TODO: Implement actual service routing logic
        return [
            'status' => 'success',
            'data' => [
                'service' => $request['service'],
                'path' => $request['path'],
                'url' => $this->serviceMap[$request['service']] . '/' . ltrim($request['path'], '/')
            ]
        ];
    }

    private function errorResponse(string $message, int $code): array
    {
        return [
            'status' => 'error',
            'code' => $code,
            'message' => $message,
            'timestamp' => date('c')
        ];
    }
}
