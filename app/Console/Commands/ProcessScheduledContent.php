<?php

namespace App\Console\Commands;

use App\Models\Content;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessScheduledContent extends Command
{
    protected $signature = 'content:process-scheduled';
    protected $description = 'Process scheduled content publishing and expiration';

    public function handle()
    {
        $this->processPublishing();
        $this->processArchiving();
        $this->processExpiration();
        $this->processVersionExpiration();
    }

    protected function processPublishing()
    {
        Content::scheduled()
            ->where('publish_at', '<=', now())
            ->each(function ($content) {
                $content->publish();
                Log::info("Published scheduled content ID: {$content->id}");
            });
    }

    protected function processArchiving()
    {
        Content::published()
            ->where('archive_at', '<=', now())
            ->each(function ($content) {
                $content->archive();
                Log::info("Archived content ID: {$content->id}");
            });
    }

    protected function processExpiration()
    {
        Content::where('expire_at', '<=', now())
            ->where('lifecycle_status', '!=', 'expired')
            ->each(function ($content) {
                $content->expire();
                Log::info("Expired content ID: {$content->id}");
            });
    }

    protected function processVersionExpiration()
    {
        ContentVersion::where('expire_at', '<=', now())
            ->where('version_status', '!=', 'expired')
            ->each(function ($version) {
                $version->markExpired();
                Log::info("Expired content version ID: {$version->id}");
            });
    }
}