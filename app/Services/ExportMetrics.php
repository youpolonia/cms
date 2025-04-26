<?php

namespace App\Services;

use App\Models\AnalyticsExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExportMetrics
{
    public function recordExportCompletion(AnalyticsExport $export, float $processingTime): void
    {
        DB::table('export_metrics')->insert([
            'export_id' => $export->id,
            'status' => 'completed',
            'processing_time' => $processingTime,
            'file_size' => Storage::size($export->file_path),
            'record_count' => $this->countRecords($export->file_path),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function recordExportFailure(AnalyticsExport $export, string $error): void
    {
        DB::table('export_metrics')->insert([
            'export_id' => $export->id,
            'status' => 'failed',
            'error' => $error,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function getSuccessRate(Carbon $from, Carbon $to): float
    {
        $results = DB::table('export_metrics')
            ->select(DB::raw('
                COUNT(*) as total,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successes
            '))
            ->whereBetween('created_at', [$from, $to])
            ->first();

        return $results->total > 0 ? ($results->successes / $results->total) * 100 : 0;
    }

    public function getAverageProcessingTime(Carbon $from, Carbon $to): float
    {
        return (float) DB::table('export_metrics')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->avg('processing_time');
    }

    protected function countRecords(string $filePath): int
    {
        try {
            $contents = Storage::get($filePath);
            return count(explode("\n", $contents)) - 1; // Subtract header
        } catch (\Exception $e) {
            return 0;
        }
    }
}