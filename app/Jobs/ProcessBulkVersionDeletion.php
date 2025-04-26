<?php

namespace App\Jobs;

use App\Models\ContentVersion;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessBulkVersionDeletion implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $versionIds,
        private int $userId
    ) {}

    public function handle()
    {
        $versions = ContentVersion::whereIn('id', $this->versionIds)->get();

        foreach ($versions as $version) {
            try {
                $version->delete();
                Log::info("Deleted version {$version->id} by user {$this->userId}");
            } catch (\Exception $e) {
                Log::error("Failed to delete version {$version->id}: " . $e->getMessage());
            }
        }
    }
}