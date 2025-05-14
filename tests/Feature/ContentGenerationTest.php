<?php

namespace Tests\Feature;

use App\Services\MCPContentGenerationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ContentGenerationTest extends TestCase
{
    use DatabaseTransactions, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockService = $this->mock(MCPContentGenerationService::class);
    }

    #[Test]
    public function it_generates_content(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $expectedResponse = ['content' => 'Generated content'];
        $this->mockService
            ->shouldReceive('generateContent')
            ->once()
            ->andReturn(['status' => 200, 'data' => $expectedResponse]);

        $response = $this->postJson('/api/content-generation/generate', [
            'prompt' => 'Test prompt',
            'content_type' => 'article',
            'tone' => 'professional',
            'length' => 500
        ]);

        $response->assertStatus(200)
            ->assertJson($expectedResponse);
    }

    #[Test]
    public function it_handles_content_generation_errors(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $this->mockService
            ->shouldReceive('generateContent')
            ->once()
            ->andReturn(['error' => 'Generation failed', 'status' => 500]);

        $response = $this->postJson('/api/content-generation/generate', [
            'prompt' => 'Test prompt',
            'content_type' => 'article'
        ]);

        $response->assertStatus(500)
            ->assertJson(['error' => 'Generation failed']);
    }

    #[Test]
    public function it_generates_summaries(): void
    {
        $expectedResponse = ['summary' => 'Generated summary'];
        $this->mockService
            ->shouldReceive('generateSummary')
            ->once()
            ->andReturn(['status' => 200, 'data' => $expectedResponse]);

        $response = $this->postJson('/api/content-generation/summarize', [
            'content' => 'Long content to summarize',
            'length' => 100
        ]);

        $response->assertStatus(200)
            ->assertJson($expectedResponse);
    }

    #[Test]
    public function it_generates_seo_metadata(): void
    {
        $expectedResponse = [
            'title' => 'SEO Title',
            'description' => 'SEO Description',
            'keywords' => ['keyword1', 'keyword2']
        ];
        $this->mockService
            ->shouldReceive('generateSeo')
            ->once()
            ->andReturn(['status' => 200, 'data' => $expectedResponse]);

        $response = $this->postJson('/api/content-generation/seo', [
            'content' => 'Content for SEO',
            'keywords' => ['test']
        ]);

        $response->assertStatus(200)
            ->assertJson($expectedResponse);
    }

    #[Test]
    public function it_performs_bulk_content_generation(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $expectedResponses = [
            ['content' => 'Content 1'],
            ['content' => 'Content 2']
        ];
        $this->mockService
            ->shouldReceive('generateContent')
            ->twice()
            ->andReturn(
                ['status' => 200, 'data' => $expectedResponses[0]],
                ['status' => 200, 'data' => $expectedResponses[1]]
            );

        $response = $this->postJson('/api/content-generation/bulk/generate', [
            'requests' => [
                [
                    'prompt' => 'Prompt 1',
                    'content_type' => 'article'
                ],
                [
                    'prompt' => 'Prompt 2',
                    'content_type' => 'blog'
                ]
            ]
        ]);

        $response->assertStatus(200)
            ->assertJson($expectedResponses);
    }

    private function createUser()
    {
        return \App\Models\User::factory()->create([
            'role_id' => 1 // Basic user role that doesn't require theme approvals
        ]);
    }
}