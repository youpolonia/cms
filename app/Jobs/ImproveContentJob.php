<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImproveContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $content,
        protected string $tone,
        protected string $style,
        protected string $length,
        protected string $cacheKey,
        protected ?int $userId = null,
        protected string $model = 'gpt-4'
    ) {}

    public function handle()
    {
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            $user?->incrementAiUsage();
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('mcp.api_key')
        ])->post(config('mcp.content_improvement.url') . '/improve/content', [
            'content' => $this->content,
            'model' => $this->model,
            'tone' => $this->tone,
            'style' => $this->style,
            'length' => $this->length
        ]);

        if ($response->successful()) {
            cache()->put(
                $this->cacheKey, 
                $response->json(), 
                now()->addSeconds(config('mcp.content_improvement.cache_ttl', 3600))
            );
            return;
        }

        Log::error('Async content improvement failed', [
            'status' => $response->status(),
            'error' => $response->body()
        ]);
        
        throw new \Exception('Content improvement failed: ' . $response->body());
    }
}