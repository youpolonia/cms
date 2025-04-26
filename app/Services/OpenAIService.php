<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class OpenAIService
{
    protected string $apiKey;
    protected ?string $organization = null;
    protected string $model;
    protected int $requestTimeout;
    protected array $generationParams;
    protected array $costTracking;

    public function __construct()
    {
        $this->apiKey = config('openai.api_key');
        $this->organization = config('openai.organization');
        $this->model = config('openai.default_model');
        $this->requestTimeout = config('openai.request_timeout');
        $this->generationParams = config('openai.generation');
        $this->costTracking = config('openai.cost_tracking');
    }

    public function generateContent(
        string $prompt,
        string $template = 'content_suggestion',
        string $outputFormat = 'text',
        bool $includeImages = false
    ): array {
        $context = ['template' => $template];
        try {
            $this->checkRateLimits();

            $messages = $this->buildMessages($prompt, $context, $outputFormat, $includeImages);
            $response = $this->makeApiRequest($messages);

            $result = [
                'content' => $this->processResponse($response, $outputFormat),
                'usage' => $response['usage'],
                'cost' => $this->calculateCost($response['usage']['total_tokens'])
            ];

            if ($includeImages) {
                $images = $this->generateImages($prompt);
                $result['images'] = $images;
                $result['cost'] += $this->calculateImageCost(count($images));
            }

            $this->trackCost($result['cost']);

            return $result;
        } catch (\Exception $e) {
            Log::error('OpenAI API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function buildMessages(
        string $prompt,
        array $context,
        string $outputFormat,
        bool $includeImages
    ): array {
        $systemPrompts = [
            'content_suggestion' => 'You are a content suggestion assistant. Provide creative and engaging content ideas based on the user\'s prompt.',
            'seo_optimization' => 'You are an SEO expert. Provide SEO optimization suggestions for the given content.',
            'content_enhancement' => 'You are a content editor. Suggest improvements to make the content more engaging and effective.',
            'content_summary' => 'You are a summarization tool. Provide concise summaries of the given content.',
            'html_content' => 'You are a web content generator. Generate complete HTML content sections based on the prompt.',
            'json_content' => 'You are a structured content generator. Provide content in JSON format with title, body, and metadata fields.'
        ];

        $systemMessage = $systemPrompts[$template] ?? $systemPrompts['content_suggestion'];

        // Add format instructions
        if ($outputFormat === 'html') {
            $systemMessage .= ' Return the content as well-formatted HTML.';
        } elseif ($outputFormat === 'json') {
            $systemMessage .= ' Return the content as valid JSON with title, content, and metadata fields.';
        }

        $messages = [
            ['role' => 'system', 'content' => $systemMessage],
            ['role' => 'user', 'content' => $prompt]
        ];

        if (!empty($context)) {
            $messages[] = ['role' => 'assistant', 'content' => json_encode($context)];
        }

        return $messages;
    }

    protected function generateImages(string $prompt): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.openai.com/v1/images/generations', [
            'prompt' => $prompt,
            'n' => 1,
            'size' => '512x512',
            'response_format' => 'url'
        ]);

        if ($response->failed()) {
            throw new \Exception('Image generation failed: ' . ($response->json()['error']['message'] ?? 'Unknown error'));
        }

        return $response->json()['data'];
    }

    protected function calculateImageCost(int $imageCount): float
    {
        return $imageCount * $this->costTracking['image_cost'];
    }

    protected function makeApiRequest(array $messages): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json'
        ];
        
        if ($this->organization) {
            $headers['OpenAI-Organization'] = $this->organization;
        }
        
        $response = Http::withHeaders($headers)
        ->timeout($this->requestTimeout)
        ->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => $messages,
            'max_tokens' => $this->generationParams['max_tokens'],
            'temperature' => $this->generationParams['temperature'],
            'presence_penalty' => $this->generationParams['presence_penalty'],
            'frequency_penalty' => $this->generationParams['frequency_penalty'],
        ]);

        if ($response->failed()) {
            throw new \Exception($response->json()['error']['message'] ?? 'OpenAI API request failed');
        }

        return $response->json();
    }

    protected function processResponse(array $response, string $outputFormat): array|string
    {
        $content = $response['choices'][0]['message']['content'] ?? '';

        if ($outputFormat === 'json') {
            return json_decode($content, true) ?: ['error' => 'Invalid JSON response'];
        }

        return $outputFormat === 'html'
            ? ['html' => $content]
            : array_filter(explode("\n", $content));
    }

    protected function checkRateLimits(): void
    {
        $key = 'openai_rate_limit_' . auth()->id();
        $requests = Cache::get($key, 0);

        if ($requests >= config('openai.rate_limit.requests_per_minute')) {
            throw new \Exception('OpenAI API rate limit exceeded');
        }

        Cache::put($key, $requests + 1, Carbon::now()->addMinute());
    }

    protected function trackCost(int $tokens): void
    {
        if (!$this->costTracking['enabled']) {
            return;
        }

        $user = auth()->user();
        if ($user && method_exists($user, 'incrementAiUsageCount')) {
            $user->incrementAiUsageCount($tokens);
        }
    }

    protected function calculateCost(int $tokens): float
    {
        return $tokens * ($this->costTracking['price_per_token'][$this->model] ?? 0);
    }
}
