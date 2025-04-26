<?php

namespace Tests\Feature;

use App\Services\OpenAIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class OpenAIIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected OpenAIService $openAIService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->openAIService = app(OpenAIService::class);
        Config::set('openai.api_key', env('OPENAI_API_KEY'));
    }

    public function test_content_generation()
    {
        $result = $this->openAIService->generateContent([
            'prompt' => 'Test content generation',
            'content_type' => 'blog_post',
            'tone' => 'professional',
            'length' => 'short',
            'style' => 'informative',
            'target_audience' => 'developers'
        ]);

        $this->assertArrayHasKey('content', $result);
        $this->assertArrayHasKey('usage', $result);
        $this->assertArrayHasKey('cost', $result);
    }

    public function test_rate_limiting()
    {
        // First request should succeed
        $response1 = $this->postJson('/api/ai/generate', [
            'prompt' => 'Test rate limiting',
            'content_type' => 'blog_post',
            'tone' => 'professional',
            'length' => 'short',
            'style' => 'informative',
            'target_audience' => 'developers'
        ]);
        $response1->assertStatus(200);

        // Second request should be rate limited
        $response2 = $this->postJson('/api/ai/generate', [
            'prompt' => 'Test rate limiting',
            'content_type' => 'blog_post',
            'tone' => 'professional',
            'length' => 'short',
            'style' => 'informative',
            'target_audience' => 'developers'
        ]);
        $response2->assertStatus(429);
    }

    public function test_usage_stats_endpoint()
    {
        // Make a request first to generate some usage
        $this->postJson('/api/ai/generate', [
            'prompt' => 'Test usage stats',
            'content_type' => 'blog_post',
            'tone' => 'professional',
            'length' => 'short',
            'style' => 'informative',
            'target_audience' => 'developers'
        ]);

        $response = $this->getJson('/api/ai/usage-stats');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_requests',
            'total_tokens',
            'total_cost',
            'daily_usage' => [
                '*' => [
                    'date',
                    'requests',
                    'tokens',
                    'cost'
                ]
            ]
        ]);
    }
}