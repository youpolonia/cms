<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\McpService;

class InstallMcpServersCommand extends Command
{
    protected $signature = 'mcp:install {--pending : Install only pending servers}';
    protected $description = 'Install and configure MCP servers';

    public function handle(McpService $mcpService)
    {
        $pendingOnly = $this->option('pending');
        
        $this->info('Starting MCP server installation...');
        
        // Get list of servers to install
        $servers = $pendingOnly 
            ? $mcpService->getPendingServers()
            : $mcpService->getAllServers();

        if (empty($servers)) {
            $this->info('No servers to install');
            return 0;
        }

        $this->table(
            ['Server', 'Status', 'Type'],
            array_map(fn($s) => [$s['name'], $s['status'], $s['type']], $servers)
        );

        if (!$this->confirm('Proceed with installation?')) {
            return 0;
        }

        $progress = $this->output->createProgressBar(count($servers));
        $progress->start();

        foreach ($servers as $server) {
            try {
                $mcpService->installServer($server);
                $progress->advance();
            } catch (\Exception $e) {
                $this->error("Failed to install {$server['name']}: " . $e->getMessage());
            }
        }

        $progress->finish();
        $this->newLine();
        $this->info('Installation completed');

        return 0;
    }
}
