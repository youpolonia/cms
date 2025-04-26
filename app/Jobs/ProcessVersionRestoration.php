<?php

namespace App\Jobs;

use App\Models\ContentVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVersionRestoration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private int $versionId,
        private int $userId
    ) {}

    public function handle()
    {
        $version = ContentVersion::with('content')->find($this->versionId);

        try {
            // Create new version based on restored content
            $newVersion = $version->content->versions()->create([
                'content' => $version->content,
                'version_number' => $version->content->versions()->max('version_number') + 1,
                'status' => 'draft',
                'restored_from_version' => $version->id,
                'restored_by' => $this->userId
            ]);

            Log::info("Restored version {$version->id} as new version {$newVersion->id} by user {$this->userId}");
        } catch (\Exception $e) {
            Log::error("Failed to restore version {$version->id}: " . $e->getMessage());
            throw $e;
        }
    }
}