<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class MCPVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Log::spy();
    }

    public function test_mcp_connectivity_verification()
    {
        // Mock the MCP tool response
        $mockResponse = [
            'data' => 'Simulated cached file content for app/Models/ApprovalWorkflow.php',
            'error' => null
        ];
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_cached_file', ['path' => 'app/Models/ApprovalWorkflow.php'])
            ->andReturn($mockResponse);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('Running Server Connectivity verification...')
            ->expectsOutput('MCP server connectivity verified')
            ->assertExitCode(0);
    }

    public function test_protocol_version_verification()
    {
        // Mock the MCP tool response
        $mockResponse = [
            'data' => '1.0.0',
            'error' => null
        ];
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_protocol_version', [])
            ->andReturn($mockResponse);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('Running Protocol Version verification...')
            ->expectsOutput('MCP protocol version verified: 1.0.0')
            ->assertExitCode(0);
    }

    public function test_failed_connectivity_verification()
    {
        // Mock failed MCP tool response
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_cached_file', ['path' => 'app/Models/ApprovalWorkflow.php'])
            ->andThrow(new \RuntimeException('Connection failed'));

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('Running Server Connectivity verification...')
            ->expectsOutput('Failed to verify MCP server connectivity')
            ->assertExitCode(0);
    }

    public function test_failed_protocol_version_verification()
    {
        // Mock failed MCP tool response
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_protocol_version', [])
            ->andReturn([
                'data' => null,
                'error' => 'Version check failed'
            ]);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('Running Protocol Version verification...')
            ->expectsOutput('Failed to verify MCP protocol version')
            ->assertExitCode(0);
    }

    public function test_server_health_verification()
    {
        // Mock the MCP tool response
        $mockResponse = [
            'data' => [
                'status' => 'healthy',
                'uptime' => '99.99%',
                'load' => '0.5'
            ],
            'error' => null
        ];
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('check_server_health', [])
            ->andReturn($mockResponse);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('MCP server health verified: healthy')
            ->assertExitCode(0);
    }

    public function test_failed_server_health_verification()
    {
        // Mock failed MCP tool response
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('check_server_health', [])
            ->andReturn([
                'data' => null,
                'error' => 'Health check failed'
            ]);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('Failed to verify MCP server health')
            ->assertExitCode(0);
    }

    public function test_authentication_verification()
    {
        // Mock the MCP tool response
        $mockResponse = [
            'data' => [
                'authenticated' => true,
                'permissions' => ['read', 'write', 'execute']
            ],
            'error' => null
        ];
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('verify_authentication', [])
            ->andReturn($mockResponse);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('MCP authentication verified')
            ->assertExitCode(0);
    }

    public function test_failed_authentication_verification()
    {
        // Mock failed MCP tool response
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('verify_authentication', [])
            ->andReturn([
                'data' => null,
                'error' => 'Authentication failed'
            ]);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('Failed to verify MCP authentication')
            ->assertExitCode(0);
    }

    public function test_resource_availability_verification()
    {
        // Mock the MCP tool response
        $mockResponse = [
            'data' => [
                'resources' => [
                    'memory' => '4GB available',
                    'storage' => '500GB free',
                    'network' => '100Mbps'
                ]
            ],
            'error' => null
        ];
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('check_resource_availability', [])
            ->andReturn($mockResponse);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('MCP resource availability verified')
            ->assertExitCode(0);
    }

    public function test_failed_resource_availability_verification()
    {
        // Mock failed MCP tool response
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('check_resource_availability', [])
            ->andReturn([
                'data' => null,
                'error' => 'Resource check failed'
            ]);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('Failed to verify MCP resource availability')
            ->assertExitCode(0);
    }

    public function test_performance_metrics_verification()
    {
        // Mock the MCP tool response
        $mockResponse = [
            'data' => [
                'response_time' => '50ms',
                'throughput' => '1000 req/sec',
                'error_rate' => '0.1%'
            ],
            'error' => null
        ];
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_performance_metrics', [])
            ->andReturn($mockResponse);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('MCP performance metrics verified')
            ->assertExitCode(0);

        // Verify logging
        Log::shouldHaveReceived('info')
            ->with('MCP performance metrics verified', ['metrics' => $mockResponse['data']]);
    }

    public function test_failed_performance_metrics_verification()
    {
        // Mock failed MCP tool response
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_performance_metrics', [])
            ->andReturn([
                'data' => null,
                'error' => 'Performance check failed'
            ]);

        // Run the verification command
        $this->artisan('mcp:verify')
            ->expectsOutput('Failed to verify MCP performance metrics')
            ->assertExitCode(0);

        // Verify error logging
        Log::shouldHaveReceived('error')
            ->with('MCP performance metrics verification failed: Invalid metrics data');
    }

    public function test_complete_verification_flow_with_all_checks_passing()
    {
        // Mock all verification responses
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_cached_file', ['path' => 'app/Models/ApprovalWorkflow.php'])
            ->andReturn(['data' => 'content', 'error' => null])
            ->shouldReceive('execute')
            ->with('get_protocol_version', [])
            ->andReturn(['data' => '1.0.0', 'error' => null])
            ->shouldReceive('execute')
            ->with('check_server_health', [])
            ->andReturn(['data' => ['status' => 'healthy'], 'error' => null])
            ->shouldReceive('execute')
            ->with('verify_authentication', [])
            ->andReturn(['data' => ['authenticated' => true], 'error' => null])
            ->shouldReceive('execute')
            ->with('check_resource_availability', [])
            ->andReturn(['data' => ['resources' => []], 'error' => null])
            ->shouldReceive('execute')
            ->with('get_performance_metrics', [])
            ->andReturn(['data' => ['response_time' => '50ms'], 'error' => null]);

        $this->artisan('mcp:verify')
            ->expectsOutput('MCP verification completed successfully')
            ->assertExitCode(0);
    }

    public function test_complete_verification_flow_with_failed_check()
    {
        // Mock one failing verification
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_cached_file', ['path' => 'app/Models/ApprovalWorkflow.php'])
            ->andReturn(['data' => 'content', 'error' => null])
            ->shouldReceive('execute')
            ->with('get_protocol_version', [])
            ->andReturn(['data' => null, 'error' => 'Version check failed']);

        $this->artisan('mcp:verify')
            ->expectsOutput('MCP verification completed with errors')
            ->assertExitCode(0);
    }

    public function test_verification_stops_on_first_failure_in_test_environment()
    {
        // Mock first verification failing
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_cached_file', ['path' => 'app/Models/ApprovalWorkflow.php'])
            ->andThrow(new \RuntimeException('Connection failed'))
            ->shouldNotReceive('execute'); // Ensure no further calls are made

        $this->artisan('mcp:verify')
            ->expectsOutput('Failed to verify MCP server connectivity')
            ->assertExitCode(1);
    }

    public function test_logging_for_successful_verification()
    {
        // Mock successful verification
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->andReturn(['data' => [], 'error' => null]);

        $this->artisan('mcp:verify');

        Log::shouldHaveReceived('info')
            ->with('MCP verification completed successfully');
    }

    public function test_logging_for_failed_verification()
    {
        // Mock failed verification
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->andThrow(new \RuntimeException('Test error'));

        $this->artisan('mcp:verify');

        Log::shouldHaveReceived('error')
            ->with('MCP verification completed with errors');
    }

    public function test_logging_verification()
    {
        // Mock successful verification with detailed logging
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->andReturn(['data' => [], 'error' => null]);

        $this->artisan('mcp:verify');

        // Verify detailed logging for each verification step
        Log::shouldHaveReceived('debug')
            ->with('Starting MCP verification process');
        Log::shouldHaveReceived('info')
            ->with('MCP verification step completed', ['step' => 'connectivity']);
        Log::shouldHaveReceived('info')
            ->with('MCP verification step completed', ['step' => 'protocol_version']);
    }

    public function test_error_handling_verification()
    {
        // Mock various error scenarios
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_cached_file', ['path' => 'app/Models/ApprovalWorkflow.php'])
            ->andThrow(new \RuntimeException('Connection timeout'))
            ->shouldReceive('execute')
            ->with('get_protocol_version', [])
            ->andReturn(['data' => null, 'error' => 'Version mismatch']);

        $this->artisan('mcp:verify')
            ->expectsOutput('Failed to verify MCP server connectivity: Connection timeout')
            ->expectsOutput('Failed to verify MCP protocol version: Version mismatch')
            ->assertExitCode(1);

        // Verify error logging contains detailed error information
        Log::shouldHaveReceived('error')
            ->with('MCP verification error', ['error' => 'Connection timeout']);
    }

    public function test_backward_compatibility_verification()
    {
        // Mock older protocol version response
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_protocol_version', [])
            ->andReturn(['data' => '0.9.0', 'error' => null])
            ->shouldReceive('execute')
            ->with('get_cached_file', ['path' => 'app/Models/ApprovalWorkflow.php'])
            ->andReturn(['data' => 'content', 'error' => null]);

        $this->artisan('mcp:verify')
            ->expectsOutput('MCP protocol version verified: 0.9.0')
            ->expectsOutput('MCP server connectivity verified')
            ->assertExitCode(0);
    }

    public function test_comprehensive_error_logging()
    {
        // Mock error with stack trace
        $exception = new \RuntimeException('Test error with stack', 500);
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->andThrow($exception);

        $this->artisan('mcp:verify');

        // Verify detailed error logging with stack trace
        Log::shouldHaveReceived('error')
            ->with('MCP verification failed with exception', [
                'error' => 'Test error with stack',
                'stack' => $exception->getTraceAsString()
            ]);
    }

    public function test_logging_configuration_verification()
    {
        // Mock successful logging configuration check
        $mockResponse = [
            'data' => [
                'log_level' => 'debug',
                'log_retention' => '30 days',
                'log_rotation' => 'daily'
            ],
            'error' => null
        ];
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('check_logging_configuration', [])
            ->andReturn($mockResponse);

        $this->artisan('mcp:verify')
            ->expectsOutput('MCP logging configuration verified')
            ->assertExitCode(0);
    }

    public function test_failed_logging_configuration_verification()
    {
        // Mock failed logging configuration check
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('check_logging_configuration', [])
            ->andReturn([
                'data' => null,
                'error' => 'Logging configuration invalid'
            ]);

        $this->artisan('mcp:verify')
            ->expectsOutput('Failed to verify MCP logging configuration')
            ->assertExitCode(0);
    }

    public function test_detailed_performance_metrics_verification()
    {
        // Mock detailed performance metrics
        $mockResponse = [
            'data' => [
                'response_time' => '50ms',
                'throughput' => '1000 req/sec',
                'error_rate' => '0.1%',
                'cpu_usage' => '25%',
                'memory_usage' => '2GB'
            ],
            'error' => null
        ];
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_detailed_performance_metrics', [])
            ->andReturn($mockResponse);

        $this->artisan('mcp:verify')
            ->expectsOutput('MCP detailed performance metrics verified')
            ->assertExitCode(0);
    }

    public function test_complete_verification_with_new_checks()
    {
        // Mock all verification responses including new checks
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_cached_file', ['path' => 'app/Models/ApprovalWorkflow.php'])
            ->andReturn(['data' => 'content', 'error' => null])
            ->shouldReceive('execute')
            ->with('get_protocol_version', [])
            ->andReturn(['data' => '1.0.0', 'error' => null])
            ->shouldReceive('execute')
            ->with('check_server_health', [])
            ->andReturn(['data' => ['status' => 'healthy'], 'error' => null])
            ->shouldReceive('execute')
            ->with('verify_authentication', [])
            ->andReturn(['data' => ['authenticated' => true], 'error' => null])
            ->shouldReceive('execute')
            ->with('check_resource_availability', [])
            ->andReturn(['data' => ['resources' => []], 'error' => null])
            ->shouldReceive('execute')
            ->with('get_performance_metrics', [])
            ->andReturn(['data' => ['response_time' => '50ms'], 'error' => null])
            ->shouldReceive('execute')
            ->with('check_logging_configuration', [])
            ->andReturn(['data' => ['log_level' => 'debug'], 'error' => null])
            ->shouldReceive('execute')
            ->with('get_detailed_performance_metrics', [])
            ->andReturn(['data' => ['cpu_usage' => '25%'], 'error' => null]);

        $this->artisan('mcp:verify')
            ->expectsOutput('MCP verification completed successfully')
            ->assertExitCode(0);
    }

    public function test_verification_order_and_failure_handling()
    {
        // Test that verification stops after first failure when configured
        $this->mock(\App\Services\MCPKnowledgeServer::class)
            ->shouldReceive('execute')
            ->with('get_cached_file', ['path' => 'app/Models/ApprovalWorkflow.php'])
            ->andThrow(new \RuntimeException('Connection failed'))
            ->shouldNotReceive('execute'); // Ensure no further calls are made

        config(['mcp.fail_fast' => true]);

        $this->artisan('mcp:verify')
            ->expectsOutput('Failed to verify MCP server connectivity: Connection failed')
            ->assertExitCode(0);
    }
}
