<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use App\Notifications\AnalyticsExportDeleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredExports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $expiredExports = AnalyticsExport::where('expires_at', '<=', now())
            ->whereNotNull('file_path')
            ->get();

        foreach ($expiredExports as $export) {
            try {
                if (Storage::exists($export->file_path)) {
                    Storage::delete($export->file_path);
                }
                $fileName = basename($export->file_path);
                $export->user->notify(new AnalyticsExportDeleted($fileName));
                $export->delete();
            } catch (\Exception $e) {
                report($e);
                continue;
            }
        }
    }
}