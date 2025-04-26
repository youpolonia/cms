<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\MCPContentGenerationService;

class GenerateSEOJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $content,
        protected string $model,
        protected string $focusKeyword,
        protected string $tone,
        protected string $cacheKey,
        protected ?int $userId = null
    ) {}

    public function handle(MCPContentGenerationService $contentService)
    {
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            $user?->incrementAiUsage();
        }

        $response = $contentService->generateSeo([
            'content' => $this->content,
            'model' => $this->model,
            'focus_keyword' => $this->focusKeyword,
            'tone' => $this->tone
        ]);

        if (isset($response['error'])) {
            Log::error('Async SEO generation failed', [
                'error' => $response['error']
            ]);
            throw new \Exception('SEO generation failed: ' . $response['error']);
        }

        cache()->put(
            $this->cacheKey,
            $response,
            now()->addSeconds(config('mcp.seo.cache_ttl', 3600))
        );
    }
}