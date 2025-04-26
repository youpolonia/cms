<?php

namespace App\Console\Commands;

use App\Jobs\ExportAnalyticsData;
use App\Models\ScheduledExport;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class ProcessScheduledExports extends Command
{
    protected $signature = 'exports:process-scheduled';
    protected $description = 'Process all scheduled exports that are due to run';

    public function handle()
    {
        $now = Carbon::now();
        $exports = ScheduledExport::query()
            ->where('is_active', true)
            ->where(function($query) use ($now) {
                $query->whereNull('last_run_at')
                    ->orWhere(function($query) use ($now) {
                        $query->where('frequency', 'daily')
                            ->whereDate('last_run_at', '<', $now->toDateString());
                    })
                    ->orWhere(function($query) use ($now) {
                        $query->where('frequency', 'weekly')
                            ->whereDate('last_run_at', '<', $now->subWeek()->toDateString());
                    })
                    ->orWhere(function($query) use ($now) {
                        $query->where('frequency', 'monthly')
                            ->whereDate('last_run_at', '<', $now->subMonth()->toDateString());
                    });
            })
            ->get();

        foreach ($exports as $export) {
            try {
                ExportAnalyticsData::dispatch($export);
                $this->info("Dispatched export job for scheduled export ID: {$export->id}");
            } catch (\Exception $e) {
                $this->error("Failed to dispatch export job for scheduled export ID: {$export->id}");
                $this->error($e->getMessage());
            }
        }

        $this->info("Processed {$exports->count()} scheduled exports");
    }
}