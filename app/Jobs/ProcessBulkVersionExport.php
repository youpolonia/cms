<?php

namespace App\Jobs;

use App\Models\ContentVersion;
use App\Services\VersionExporter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessBulkVersionExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private array $versionIds,
        private string $format,
        private int $userId
    ) {}

    public function handle(VersionExporter $exporter)
    {
        $versions = ContentVersion::with('content')
            ->whereIn('id', $this->versionIds)
            ->get();

        $exportPath = $exporter->export($versions, $this->format);

        // Notify user when export is complete
        Storage::disk('exports')->put(
            "exports/user_{$this->userId}/versions_export.{$this->format}",
            file_get_contents($exportPath)
        );
    }
}