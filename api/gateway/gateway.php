<?php
declare(strict_types=1);

namespace Api\Gateway;

class Gateway
{
    private array $serviceMap = [];
    private array $middlewares = [];

    public function __construct(array $config = [])
    {
        $this->serviceMap = $config['services'] ?? [];
    }

    public function addMiddleware(callable $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function handleRequest(array $request): array
    {
        try {
            $request = $this->applyMiddlewares($request);
            return $this->routeRequest($request);
        } catch (\Exception $e) {
            return [
                'status' => 'error',
                'code' => $e->getCode(),
                'message' => $e->getMessage()
            ];
        }
    }

    private function applyMiddlewares(array $request): array
    {
        foreach ($this->middlewares as $middleware) {
            $request = $middleware($request);
        }
        return $request;
    }

    private function routeRequest(array $request): array
    {
        $path = $request['path'] ?? '';
        $service = $this->serviceMap[$path] ?? null;

        if (!$service) {
            throw new \RuntimeException("Service not found", 404);
        }

        return $this->forwardRequest($service, $request);
    }

    private function forwardRequest(array $service, array $request): array
    {
        $ch = curl_init($service['url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $request['headers'] ?? [],
            CURLOPT_POSTFIELDS => $request['body'] ?? null,
            CURLOPT_CUSTOMREQUEST => $request['method'] ?? 'GET'
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => 'success',
            'code' => $status,
            'data' => json_decode($response, true) ?? $response
        ];
    }
}
