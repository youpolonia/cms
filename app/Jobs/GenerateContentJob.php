<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\MCPContentGenerationService;

class GenerateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $prompt,
        protected string $model,
        protected string $cacheKey,
        protected ?int $userId = null,
        protected string $contentType = 'blog_post',
        protected int $maxTokens = 1000,
        protected bool $seoOptimized = false,
        protected ?string $tone = null,
        protected ?string $style = null
    ) {}

    public function handle(MCPContentGenerationService $contentService)
    {
        $this->validateParameters();
        
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            $user?->incrementAiUsage();
        }
        
        $contentTypeConfig = config("mcp.content_generation.content_types.{$this->contentType}");
        $defaultTone = $contentTypeConfig['default_tone'] ?? null;
        
        $response = $contentService->generateContent([
            'prompt' => $this->prompt,
            'model' => $this->model,
            'content_type' => $this->contentType,
            'max_tokens' => min($this->maxTokens, $contentTypeConfig['max_tokens']),
            'seo_optimized' => $this->seoOptimized,
            'tone' => $this->tone ?? $defaultTone,
            'style' => $this->style
        ]);
        
        $this->handleResponse($response);
    }
    
    protected function validateParameters(): void
    {
        $contentTypes = array_keys(config('mcp.content_generation.content_types'));
        $tones = config('mcp.content_generation.tones');
        $styles = config('mcp.content_generation.styles');
        
        if (!in_array($this->contentType, $contentTypes)) {
            throw new \InvalidArgumentException("Invalid content type. Supported types: " . implode(', ', $contentTypes));
        }
        
        if ($this->tone && !in_array($this->tone, $tones)) {
            throw new \InvalidArgumentException("Invalid tone. Supported tones: " . implode(', ', $tones));
        }
        
        if ($this->style && !in_array($this->style, $styles)) {
            throw new \InvalidArgumentException("Invalid style. Supported styles: " . implode(', ', $styles));
        }
    }
    
    protected function handleResponse(array $response): void
    {
        if (!empty($response['error'])) {
            Log::error('Content generation failed', [
                'error' => $response['error'],
                'request' => [
                    'content_type' => $this->contentType,
                    'model' => $this->model
                ]
            ]);
            throw new \Exception('Content generation failed: ' . $response['error']);
        }

        cache()->put($this->cacheKey, $response, now()->addSeconds(config('mcp.content_generation.cache_ttl')));
    }
}