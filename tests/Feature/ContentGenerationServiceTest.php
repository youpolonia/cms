<?php

namespace Tests\Feature;

use App\Services\ContentGenerationService;
use App\Services\PhpContentGenerationService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContentGenerationServiceTest extends TestCase
{
    public function test_it_detects_node_server()
    {
        Http::fake([
            'http://localhost:8080/ping' => Http::response([], 200),
            'http://localhost:8080/generate/content' => Http::response([
                'status' => 'success',
                'content' => 'Node.js content'
            ], 200, ['Content-Type' => 'application/json'])
        ]);

        $service = new ContentGenerationService(
            $this->createMock(PhpContentGenerationService::class)
        );

        $this->assertTrue($service->detectNodeServer());
    }

    public function test_it_falls_back_to_php_when_node_unavailable()
    {
        Http::fake([
            'http://localhost:8080/ping' => Http::response([], 500)
        ]);

        $phpService = $this->createMock(PhpContentGenerationService::class);
        $phpService->method('generateContent')
            ->willReturn('PHP fallback content');

        $service = new ContentGenerationService($phpService);

        $this->assertEquals('PHP fallback content', $service->generateContent('test'));
    }

    public function test_it_uses_node_when_available()
    {
        Http::fake([
            'http://localhost:8080/ping' => Http::response([], 200),
            'http://localhost:8080/generate/content' => Http::response([
                'status' => 'success',
                'content' => 'Node.js content'
            ], 200, ['Content-Type' => 'application/json'])
        ]);

        $service = new ContentGenerationService(
            $this->createMock(PhpContentGenerationService::class)
        );

        $this->assertEquals('Node.js content', $service->generateContent('test'));
    }

    public function test_response_parsing()
    {
        Http::fake([
            'http://localhost:8080/ping' => Http::response([], 200),
            'http://localhost:8080/generate/content' => Http::response([
                'status' => 'success',
                'content' => 'Test content'
            ], 200, ['Content-Type' => 'application/json'])
        ]);

        $service = new ContentGenerationService(
            $this->createMock(PhpContentGenerationService::class)
        );

        $this->assertEquals('Test content', $service->generateContent('test'));
    }
}