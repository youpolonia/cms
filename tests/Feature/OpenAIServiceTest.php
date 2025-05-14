<?php

namespace Tests\Feature;

use App\Services\OpenAIService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OpenAIServiceTest extends TestCase
{
    protected OpenAIService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OpenAIService::class);
    }

    /** @test */
    public function it_generates_content_from_prompt()
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => 'Generated content about AI'
                    ]
                ]]
            ], 200)
        ]);

        $result = $this->service->generateContent(
            'Write about {topic}',
            ['topic' => 'AI Content Generation']
        );

        $this->assertEquals('Generated content about AI', $result);
    }

    /** @test */
    public function it_validates_content_quality()
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'valid' => true,
                            'score' => 85,
                            'feedback' => 'Good content with proper structure'
                        ])
                    ]
                ]]
            ], 200)
        ]);

        $result = $this->service->validateContent(
            'Sample content to validate',
            ['readability', 'seo']
        );

        $this->assertTrue($result['valid']);
        $this->assertEquals(85, $result['score']);
    }

    /** @test */
    public function it_handles_api_errors_gracefully()
    {
        Http::fake([
            'api.openai.com/v1/chat/completions' => Http::response([], 500)
        ]);

        $result = $this->service->generateContent(
            'Write about {topic}',
            ['topic' => 'AI Content Generation']
        );

        $this->assertNull($result);
    }
}