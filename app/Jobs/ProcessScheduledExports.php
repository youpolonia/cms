<?php

namespace App\Jobs;

use App\Models\ScheduledExport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessScheduledExports implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        $exports = ScheduledExport::query()
            ->where('is_active', true)
            ->where(function($query) {
                $query->whereNull('last_run_at')
                    ->orWhere('last_run_at', '<=', now()->subHours($this->export->frequency_hours));
            })
            ->get();

        foreach ($exports as $export) {
            try {
                $jobClass = $this->getJobClassForType($export->type);
                
                if (!class_exists($jobClass)) {
                    Log::error("Export job class not found: {$jobClass}");
                    continue;
                }

                dispatch(new $jobClass($export));
                
                Log::info("Dispatched export job for {$export->type} export ID {$export->id}");
                
            } catch (\Exception $e) {
                Log::error("Failed to process export ID {$export->id}: " . $e->getMessage());
            }
        }
    }

    protected function getJobClassForType(string $type): string
    {
        return match($type) {
            'analytics' => ExportAnalyticsData::class,
            'content' => ExportContentData::class,
            'notifications' => ExportNotifications::class,
            default => throw new \InvalidArgumentException("Unknown export type: {$type}"),
        };
    }
}