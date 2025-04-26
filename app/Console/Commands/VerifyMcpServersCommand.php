<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\McpService;

class VerifyMcpServersCommand extends Command
{
    protected $signature = 'mcp:verify';
    protected $description = 'Verify MCP server connections';

    public function handle(McpService $mcpService)
    {
        $this->info('Verifying MCP server connections...');
        
        $servers = $mcpService->getAllServers();
        
        if (empty($servers)) {
            $this->info('No MCP servers configured');
            return 0;
        }

        $results = [];
        foreach ($servers as $server) {
            $status = $mcpService->verifyConnection($server['name']) 
                ? '✅ Active' 
                : '❌ Failed';
            $results[] = [
                'Server' => $server['name'],
                'Status' => $status,
                'Type' => $server['type'],
                'URL' => $server['url']
            ];
        }

        $this->table(
            ['Server', 'Status', 'Type', 'URL'],
            $results
        );

        return 0;
    }
}
