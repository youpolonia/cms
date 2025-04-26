<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyMCP extends Command
{
    protected $signature = 'mcp:verify';
    protected $description = 'Verify MCP server connectivity and protocol compliance';

    public function handle()
    {
        $overallSuccess = true;
        $failFast = config('mcp.fail_fast', false);
        
        $verifications = [
            'Server Connectivity' => fn() => $this->verifyServerConnectivity(),
            'Protocol Version' => fn() => $this->verifyProtocolVersion(),
            'Server Health' => fn() => $this->verifyServerHealth(),
            'Authentication' => fn() => $this->verifyAuthentication(),
            'Resource Availability' => fn() => $this->verifyResourceAvailability(),
            'Performance Metrics' => fn() => $this->verifyPerformanceMetrics()
        ];

        foreach ($verifications as $name => $verification) {
            $this->line("Running $name verification...");
            
            try {
                $result = $verification();
                $overallSuccess = $overallSuccess && $result;
                
                if (!$result && $failFast) {
                    break;
                }
            } catch (\Exception $e) {
                $this->error("$name verification failed: " . $e->getMessage());
                Log::error("$name verification failed", [
                    'error' => $e->getMessage(),
                    'exception' => $e
                ]);
                $overallSuccess = false;
                
                if ($failFast) {
                    break;
                }
            }
        }
        
        if ($overallSuccess) {
            $message = 'MCP verification completed successfully';
            $this->info($message);
            Log::info($message);
        } else {
            $message = 'MCP verification completed with errors';
            $this->error($message);
            Log::error($message);
        }
        
        return 0;
    }

    protected function verifyServerConnectivity()
    {
        try {
            $result = $this->callMCPTool('get_cached_file', [
                'path' => 'app/Models/ApprovalWorkflow.php'
            ]);
            
            if ($result === null) {
                $this->error('Failed to verify MCP server connectivity');
                Log::error('MCP server connectivity verification failed: No data returned');
                return false;
            }
            
            $this->info('MCP server connectivity verified');
            Log::info('MCP server connectivity verified');
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to verify MCP server connectivity');
            Log::error('MCP server connectivity verification failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function verifyProtocolVersion()
    {
        try {
            $version = $this->callMCPTool('get_protocol_version');
            
            if (empty($version)) {
                throw new \RuntimeException('No version returned');
            }

            $this->info("MCP protocol version verified: $version");
            Log::info("MCP protocol version verified: $version");
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to verify MCP protocol version: ' . $e->getMessage());
            Log::error('MCP protocol version verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function verifyServerHealth()
    {
        try {
            $health = $this->callMCPTool('check_server_health');
            
            if (empty($health) || !isset($health['status'])) {
                throw new \RuntimeException('Invalid health data');
            }

            $this->info("MCP server health verified: {$health['status']}");
            Log::info("MCP server health verified", ['health' => $health]);
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to verify MCP server health: ' . $e->getMessage());
            Log::error('MCP server health verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function verifyAuthentication()
    {
        try {
            $auth = $this->callMCPTool('verify_authentication');
            
            if (empty($auth) || !isset($auth['authenticated'])) {
                throw new \RuntimeException('Invalid auth data');
            }

            if (!$auth['authenticated']) {
                throw new \RuntimeException('Authentication failed');
            }

            $this->info('MCP authentication verified');
            Log::info('MCP authentication verified', ['permissions' => $auth['permissions'] ?? []]);
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to verify MCP authentication: ' . $e->getMessage());
            Log::error('MCP authentication verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function verifyResourceAvailability()
    {
        try {
            $resources = $this->callMCPTool('check_resource_availability');
            
            if (empty($resources) || !isset($resources['resources'])) {
                throw new \RuntimeException('Invalid resource data');
            }

            $this->info('MCP resource availability verified');
            Log::info('MCP resource availability verified', ['resources' => $resources['resources']]);
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to verify MCP resource availability: ' . $e->getMessage());
            Log::error('MCP resource availability verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function verifyPerformanceMetrics()
    {
        try {
            $metrics = $this->callMCPTool('get_performance_metrics');
            
            if (empty($metrics) || !isset($metrics['response_time'])) {
                throw new \RuntimeException('Invalid metrics data');
            }

            $this->info('MCP performance metrics verified');
            Log::info('MCP performance metrics verified', ['metrics' => $metrics]);
            return true;
        } catch (\Exception $e) {
            $this->error('Failed to verify MCP performance metrics: ' . $e->getMessage());
            Log::error('MCP performance metrics verification failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    protected function callMCPTool(string $tool, array $params = [])
    {
        try {
            $result = app(\App\Services\MCPKnowledgeServer::class)->execute($tool, $params);
            
            if (!is_array($result)) {
                throw new \RuntimeException('Invalid MCP server response format');
            }
            
            if (isset($result['error']) && $result['error']) {
                throw new \RuntimeException($result['error']);
            }
            
            return $result['data'] ?? null;
        } catch (\Exception $e) {
            throw new \RuntimeException("MCP tool $tool failed: " . $e->getMessage());
        }
    }
}
