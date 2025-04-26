<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\McpServer;

class McpService
{
    protected $config;
    protected $baseUrl;

    public function __construct()
    {
        $this->config = config('mcp');
        $this->baseUrl = $this->config['base_url'] ?? 'http://localhost:8000';
    }

    public function getAllServers(): array
    {
        return McpServer::all()->toArray();
    }

    public function getPendingServers(): array
    {
        return McpServer::where('status', 'pending')->get()->toArray();
    }

    public function installServer(array $server): bool
    {
        try {
            $response = Http::post("{$this->baseUrl}/install", [
                'name' => $server['name'],
                'type' => $server['type'],
                'config' => $server['config']
            ]);

            if ($response->successful()) {
                McpServer::where('name', $server['name'])
                    ->update(['status' => 'installed']);
                return true;
            }

            throw new \RuntimeException($response->body());
        } catch (\Exception $e) {
            Log::error("Failed to install MCP server", [
                'server' => $server['name'],
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function verifyConnection(string $serverName): bool
    {
        try {
            $server = McpServer::where('name', $serverName)->firstOrFail();
            $response = Http::get("{$server['url']}/ping");
            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Failed to verify MCP server connection", [
                'server' => $serverName,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}