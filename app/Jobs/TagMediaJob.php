<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\MCPMediaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TagMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public Media $media,
        public ?int $userId = null
    ) {
        $this->onQueue('media');
    }

    public function handle(MCPMediaService $mediaService)
    {
        if ($this->userId) {
            $user = \App\Models\User::find($this->userId);
            $user?->incrementAiUsage();
        }

        $response = $mediaService->tagMedia([
            'path' => $this->media->path,
            'mime_type' => $this->media->mime_type
        ]);

        if (isset($response['error'])) {
            Log::error('Media tagging failed', [
                'error' => $response['error'],
                'media_id' => $this->media->id
            ]);
            throw new \Exception('Media tagging failed: ' . $response['error']);
        }

        $this->media->update([
            'metadata' => array_merge(
                $this->media->metadata ?? [],
                ['tags' => $response['data']]
            )
        ]);
    }
}