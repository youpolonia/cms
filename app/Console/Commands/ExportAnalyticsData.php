<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ContentApprovalAnalyticsService;
use App\Services\ThemeApprovalAnalyticsService;
use App\Services\RollbackAnalyticsService;

class ExportAnalyticsData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:export 
        {type : The analytics type to export (content|theme)}
        {--timeframe= : The date range in format "start_date,end_date" (e.g. "2025-01-01,2025-04-16")}
        {--filter=* : Optional filters to apply (can be specified multiple times)}
        {--format=csv : The export format (csv|json)}
        {--path= : Optional path to save the export file}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export analytics data in various formats. Timeframe is required in format "YYYY-MM-DD,YYYY-MM-DD". Filters can be specified multiple times.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->argument('type');
        $format = $this->option('format');
        $path = $this->option('path');

        if (!in_array($type, ['content', 'theme'])) {
            $this->error('Invalid type. Must be "content" or "theme"');
            return 1;
        }

        if (!in_array($format, ['csv', 'json'])) {
            $this->error('Invalid format. Must be "csv" or "json"');
            return 1;
        }

        if (!$this->option('timeframe')) {
            $this->error('Timeframe is required in format "YYYY-MM-DD,YYYY-MM-DD"');
            return 1;
        }

        $timeframe = explode(',', $this->option('timeframe'));
        if (count($timeframe) !== 2 || !strtotime($timeframe[0]) || !strtotime($timeframe[1])) {
            $this->error('Invalid timeframe format. Must be "start_date,end_date" with valid dates');
            return 1;
        }

        try {
            $data = $this->fetchAnalyticsData($type);
            $export = $this->formatData($data, $format);

            if ($path) {
                file_put_contents($path, $export);
                $this->info("Exported $type analytics to $path");
            } else {
                $this->line($export);
            }

            return 0;
        } catch (\Exception $e) {
            $this->error("Export failed: " . $e->getMessage());
            return 1;
        }
    }

    protected function fetchAnalyticsData(string $type): array
    {
        $timeframe = explode(',', $this->option('timeframe'));
        $filters = $this->option('filter') ?: [];

        if ($type === 'content') {
            return [
                'completion_rates' => app(ContentApprovalAnalyticsService::class)->getCompletionRates($timeframe, $filters),
                'approval_times' => app(ContentApprovalAnalyticsService::class)->getApprovalTimes($timeframe, $filters),
                'rejection_reasons' => app(ContentApprovalAnalyticsService::class)->getRejectionReasons($timeframe, $filters),
            ];
        }

        return [
            'approval_stats' => app(ThemeApprovalAnalyticsService::class)->getApprovalStats($timeframe, $filters),
            'comparison_stats' => app(ThemeApprovalAnalyticsService::class)->getComparisonStats($timeframe, $filters),
            'rollback_stats' => app(RollbackAnalyticsService::class)->getStats($timeframe, $filters),
        ];
    }

    protected function formatData(array $data, string $format): string
    {
        if ($format === 'json') {
            return json_encode($data, JSON_PRETTY_PRINT);
        }

        // CSV formatting
        $csv = '';
        foreach ($data as $key => $values) {
            $csv .= "$key\n";
            if (isset($values[0])) {
                $csv .= implode(',', array_keys($values[0])) . "\n";
                foreach ($values as $row) {
                    $csv .= implode(',', array_values($row)) . "\n";
                }
            }
            $csv .= "\n";
        }
        return $csv;
    }
}
