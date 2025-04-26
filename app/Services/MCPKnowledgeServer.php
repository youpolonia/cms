<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class MCPKnowledgeServer extends MCPBaseService
{
    protected $client;
    protected $protocolVersion = '1.0.0';

    public function __construct()
    {
        parent::__construct();
        
        $baseUrl = config('mcp.knowledge_server.url');
        if (empty($baseUrl)) {
            throw new \RuntimeException('MCP Knowledge Server URL not configured');
        }

        $this->client = Http::baseUrl($baseUrl)
            ->timeout(config('mcp.knowledge_server.timeout', 30))
            ->retry(3, 100);
    }

    public function execute(string $tool, array $params = [])
    {
        try {
            switch ($tool) {
                case 'get_protocol_version':
                    return ['version' => $this->getProtocolVersion()];
                case 'get_performance_metrics':
                    return $this->getPerformanceMetrics();
                case 'check_server_health':
                    return $this->checkServerHealth();
                case 'verify_authentication':
                    return $this->verifyAuthentication();
                case 'check_resource_availability':
                    return $this->checkResourceAvailability();
                case 'cache_files_batch':
                    return $this->cacheFilesBatch($params['files'] ?? []);
                default:
                    throw new \RuntimeException("Unknown MCP tool: {$tool}");
            }
        } catch (\Exception $e) {
            Log::error("MCP tool execution failed", [
                'tool' => $tool,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function getPerformanceMetrics(): array
    {
        try {
            $response = $this->client->get('/metrics');
            $data = $response->json() ?? [];
            return [
                'response_time' => $response->handlerStats()['total_time'] ?? 0,
                'memory_usage' => $data['memory'] ?? 0,
                'cpu_usage' => $data['cpu'] ?? 0
            ];
        } catch (\Exception $e) {
            Log::error("Failed to get performance metrics: " . $e->getMessage());
            return [];
        }
    }

    protected function checkServerHealth(): array
    {
        try {
            $response = $this->client->get('/health');
            return [
                'status' => $response->successful() ? 'healthy' : 'unhealthy',
                'details' => $response->json() ?? []
            ];
        } catch (\Exception $e) {
            return ['status' => 'unavailable', 'error' => $e->getMessage()];
        }
    }

    protected function verifyAuthentication(): array
    {
        try {
            $response = $this->client->get('/auth/verify');
            return [
                'authenticated' => $response->successful(),
                'user' => $response->json()['user'] ?? null
            ];
        } catch (\Exception $e) {
            return ['authenticated' => false, 'error' => $e->getMessage()];
        }
    }

    protected function checkResourceAvailability(): array
    {
        try {
            $response = $this->client->get('/resources');
            $data = $response->json() ?? [];
            return [
                'available' => $data['available'] ?? false,
                'resources' => $data['resources'] ?? []
            ];
        } catch (\Exception $e) {
            return ['available' => false, 'error' => $e->getMessage()];
        }
    }

    // Existing knowledge server methods...

    public function cacheFilesBatch(array $files): array
    {
        try {
            $this->validateBatchFiles($files);
            
            $response = $this->client
                ->withHeaders([
                    'X-MCP-Version' => $this->protocolVersion,
                    'Content-Type' => 'application/json'
                ])
                ->post('/store-batch', ['files' => $files]);

            return $this->processBatchResponse($response);
        } catch (\Exception $e) {
            Log::error("MCP batch cache failed", [
                'error' => $e->getMessage(),
                'files' => array_map(fn($f) => $f['key'], $files)
            ]);
            throw $e;
        }
    }

    protected function validateBatchFiles(array $files): void
    {
        if (empty($files)) {
            throw new \InvalidArgumentException('Files array cannot be empty');
        }

        foreach ($files as $file) {
            if (!isset($file['key']) || !isset($file['value'])) {
                throw new \InvalidArgumentException(
                    'Each file must have both key and value properties'
                );
            }
        }
    }

    protected function processBatchResponse($response): array
    {
        if (!$response->successful()) {
            throw new \RuntimeException(
                "Batch request failed: " . $response->body()
            );
        }

        $data = $response->json();

        if (!isset($data['success']) || !$data['success']) {
            throw new \RuntimeException(
                $data['message'] ?? 'Unknown batch processing error'
            );
        }

        return $data['results'] ?? [];
    }
}
