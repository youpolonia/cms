<?php

namespace App\Console\Commands;

use App\Models\AnalyticsExport;
use App\Notifications\AnalyticsExportDeleted;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupAnalyticsExports extends Command
{
    protected $signature = 'analytics:cleanup-exports';
    protected $description = 'Delete expired analytics export files';

    public function handle()
    {
        $retentionDays = config('analytics.export_retention_days');
        
        if ($retentionDays === null) {
            $this->info('Export cleanup is disabled (retention period set to null).');
            return 0;
        }

        $expiredExports = AnalyticsExport::where('expires_at', '<=', now())
            ->orWhere('created_at', '<=', now()->subDays($retentionDays))
            ->get();
        
        $deletedCount = 0;
        
        foreach ($expiredExports as $export) {
            try {
                if (Storage::exists($export->file_path)) {
                    Storage::delete($export->file_path);
                }
                
                // Notify user before deleting the export record
                $export->user->notify(
                    new AnalyticsExportDeleted(basename($export->file_path))
                );
                
                $export->delete();
                $deletedCount++;
            } catch (\Exception $e) {
                $this->error("Failed to delete export {$export->id}: {$e->getMessage()}");
            }
        }

        $this->info("Deleted {$deletedCount} expired analytics exports.");
        return 0;
    }
}
