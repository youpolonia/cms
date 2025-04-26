<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use App\Services\ExportMetrics;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ArchiveExportsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected int $daysOld = 30,
        protected string $coldStorageDisk = 's3'
    ) {}

    public function handle()
    {
        $exports = AnalyticsExport::query()
            ->where('created_at', '<', now()->subDays($this->daysOld))
            ->whereNull('archived_at')
            ->get();

        foreach ($exports as $export) {
            try {
                if (!Storage::exists($export->file_path)) {
                    continue;
                }

                $coldPath = "archives/exports/{$export->id}/" . basename($export->file_path);
                Storage::disk($this->coldStorageDisk)->put(
                    $coldPath,
                    Storage::get($export->file_path)
                );

                $export->update([
                    'archived_at' => now(),
                    'archive_path' => $coldPath,
                    'original_file_path' => $export->file_path,
                    'file_path' => null
                ]);

                Storage::delete($export->file_path);

            } catch (\Exception $e) {
                app(ExportMetrics::class)->recordExportFailure(
                    $export,
                    "Archive failed: " . $e->getMessage()
                );
                continue;
            }
        }
    }
}