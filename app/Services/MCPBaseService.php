<?php

namespace App\Services;

use GuzzleHttp\Client;

class MCPBaseService
{
    protected $client;
    
    protected $serverType;

    public function __construct() {
        if (empty($this->serverType)) {
            throw new \RuntimeException('MCP service must define serverType property');
        }

        $config = config("mcp.servers.{$this->serverType}");
        
        if (empty($config)) {
            throw new \RuntimeException("Missing MCP configuration for server type: {$this->serverType}");
        }

        $this->client = new Client([
            'base_uri' => $config['base_uri'] ?? '',
            'timeout' => $config['timeout'] ?? 30,
            'headers' => [
                'Authorization' => 'Bearer ' . ($config['api_key'] ?? ''),
                'Accept' => 'application/json',
            ]
        ]);
    }
}