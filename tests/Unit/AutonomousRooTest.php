<?php

namespace Tests\Unit;

use App\Services\AutonomousRooService;
use Tests\TestCase;

class AutonomousRooTest extends TestCase
{
    public function test_executes_basic_task()
    {
        $service = new AutonomousRooService();
        $results = $service->executeAutonomousTask('test_task');
        
        $this->assertArrayHasKey('steps', $results);
        $this->assertNotEmpty($results['steps']);
    }

    public function test_handles_errors_gracefully()
    {
        // Create a mock DecisionEngine that throws an exception
        $mockEngine = $this->createMock(\App\Services\DecisionEngine::class);
        $mockEngine->method('createPlan')
            ->willThrowException(new \Exception('Simulated error'));

        // Create real service with mocked dependencies
        $service = new \App\Services\AutonomousRooService();
        $reflection = new \ReflectionClass($service);
        $property = $reflection->getProperty('decisionEngine');
        $property->setAccessible(true);
        $property->setValue($service, $mockEngine);
        
        $results = $service->executeAutonomousTask('failing_task');
        $this->assertArrayHasKey('error_handling', $results);
        $this->assertTrue($results['error_handling']['recovered']);
    }

    public function test_prioritizes_critical_tasks()
    {
        $service = new AutonomousRooService();
        $results = $service->executeAutonomousTask('URGENT_error_fix');
        
        $this->assertEquals('critical', $results['priority']);
    }
}