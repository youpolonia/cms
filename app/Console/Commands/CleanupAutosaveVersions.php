<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class CleanupAutosaveVersions extends Command
{
    protected $signature = 'versions:cleanup-autosaves';
    protected $description = 'Clean up old autosave versions based on retention settings';

    public function handle()
    {
        $retentionDays = Config::get('versions.autosave.retention_days');
        $keepMinVersions = Config::get('versions.autosave.keep_min_versions');

        if ($retentionDays === null) {
            $this->info('Autosave cleanup is disabled (retention period set to null).');
            return 0;
        }

        $cutoffDate = Carbon::now()->subDays($retentionDays);

        $deleted = DB::transaction(function () use ($cutoffDate, $keepMinVersions) {
            // First get content IDs that have more than the minimum versions
            $contentIds = DB::table('content_versions')
                ->select('content_id')
                ->where('is_autosave', true)
                ->groupBy('content_id')
                ->havingRaw('COUNT(*) > ?', [$keepMinVersions])
                ->pluck('content_id');

            // Then delete old autosaves for those contents
            return DB::table('content_versions')
                ->where('is_autosave', true)
                ->whereIn('content_id', $contentIds)
                ->where('created_at', '<', $cutoffDate)
                ->delete();
        });

        $this->info("Deleted {$deleted} old autosave versions older than {$retentionDays} days.");
        return 0;
    }
}