<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use App\Services\MCPContentGenerationService;

class GenerateSummaryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $content,
        protected string $model,
        protected int $maxLength,
        protected string $cacheKey,
        protected ?int $userId = null
    ) {}

    public function handle(MCPContentGenerationService $contentService)
    {
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            $user?->incrementAiUsage();
        }

        $response = $contentService->generateSummary([
            'content' => $this->content,
            'model' => $this->model,
            'max_length' => $this->maxLength
        ]);

        if (isset($response['error'])) {
            Log::error('Async summary generation failed', [
                'error' => $response['error']
            ]);
            throw new \Exception('Summary generation failed: ' . $response['error']);
        }

        cache()->put(
            $this->cacheKey,
            $response,
            now()->addSeconds(config('mcp.summarization.cache_ttl', 3600))
        );
    }
}