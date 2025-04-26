<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenAIServiceTest extends TestCase
{
    protected OpenAIService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OpenAIService();
        $this->user = User::factory()->create(['subscription_tier' => 'basic']);
    }

    public function test_content_suggestion()
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode(['suggestion' => 'Test suggestion'])],
                ]],
                'usage' => [
                    'prompt_tokens' => 100,
                    'completion_tokens' => 50,
                    'total_tokens' => 150
                ]
            ])
        ]);

        $result = $this->service->getContentSuggestions('Test prompt', $this->user);
        
        $this->assertEquals('Test suggestion', $result['content']['suggestion']);
        $this->assertEquals(150, $result['tokens_used']);
        $this->assertEquals(0.006, $result['cost']); // 100*0.00003 + 50*0.00006
    }

    public function test_rate_limiting()
    {
        Cache::put('openai_rate_limit_minute_' . $this->user->id, 100, now()->addMinutes(1));
        
        $this->expectExceptionMessage('Rate limit exceeded');
        $this->service->getContentSuggestions('Test', $this->user);
    }
    
    public function test_api_call_with_organization()
    {
        config(['openai.organization' => 'test-org']);
        
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode(['test' => 'success'])],
                    'usage' => ['total_tokens' => 100]
                ]]
            ])
        ]);
    
        $result = $this->service->makeApiRequest([
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => 'test']]
        ]);
    
        $this->assertEquals('success', $result['content']['test']);
    }

    public function test_api_call_without_organization()
    {
        config(['openai.organization' => null]);
        
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode(['test' => 'success'])],
                ]],
                'usage' => ['total_tokens' => 100]
            ])
        ]);

        $result = $this->service->makeApiRequest([
            'model' => 'gpt-3.5-turbo',
            'messages' => [['role' => 'user', 'content' => 'test']]
        ]);

        $this->assertEquals('success', $result['content']['test']);
    }

    public function test_circuit_breaker()
    {
        $mock = $this->getMockBuilder(OpenAIService::class)
            ->onlyMethods([])
            ->getMock();
            
        $mock->failureCount = 5;
        $mock->lastFailureTime = time();
        $mock->circuitBreaker = true;

        $this->expectExceptionMessage('Service temporarily unavailable');
        $mock->getContentSuggestions('Test', $this->user);
    }

    public function test_prompt_templates()
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode(['suggestion' => 'SEO suggestion'])],
                ]],
                'usage' => ['total_tokens' => 100]
            ])
        ]);

        $result = $this->service->getContentSuggestions(
            'SEO content', 
            $this->user, 
            [], 
            'seo_optimization'
        );

        $this->assertEquals('SEO suggestion', $result['content']['suggestion']);
    }

    public function test_usage_tracking()
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => ['content' => json_encode(['suggestion' => 'Test'])],
                ]],
                'usage' => [
                    'prompt_tokens' => 100,
                    'completion_tokens' => 50,
                    'total_tokens' => 150
                ]
            ])
        ]);

        $initialCount = $this->user->ai_usage_count;
        $initialCost = $this->user->ai_usage_cost;

        $this->service->getContentSuggestions('Test', $this->user);

        $this->user->refresh();
        $this->assertEquals($initialCount + 150, $this->user->ai_usage_count);
        $this->assertEquals($initialCost + 0.006, $this->user->ai_usage_cost);
    }
}