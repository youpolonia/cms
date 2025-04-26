<?php

namespace App\Jobs;

use App\Models\AnalyticsExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ExportExtension implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public AnalyticsExport $export
    ) {}

    public function handle()
    {
        $maxExtensions = config('analytics.exports.max_extensions');
        $extendDays = config('analytics.exports.extend_days');

        if ($this->export->extension_count >= $maxExtensions) {
            return false;
        }

        $this->export->update([
            'expires_at' => $this->export->expires_at->addDays($extendDays),
            'extension_count' => $this->export->extension_count + 1,
            'last_extended_at' => now()
        ]);

        return true;
    }
}