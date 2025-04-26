<?php

namespace App\Services;

use App\Models\AnalyticsExport;
use App\Models\ExportCost;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExportCostService
{
    protected $costRates = [
        'processing' => 0.02, // $ per minute
        'storage' => 0.01, // $ per MB per day
        'bandwidth' => 0.10, // $ per GB
        'compute' => 0.05 // $ per vCPU minute
    ];

    /**
     * Track export costs
     */
    public function trackCosts(int $exportId): ExportCost
    {
        $export = AnalyticsExport::findOrFail($exportId);

        $costs = $this->calculateCosts($export);

        return ExportCost::updateOrCreate(
            ['export_id' => $exportId],
            [
                'processing_cost' => $costs['processing'],
                'storage_cost' => $costs['storage'],
                'bandwidth_cost' => $costs['bandwidth'],
                'compute_cost' => $costs['compute'],
                'total_cost' => array_sum($costs),
                'currency' => 'USD',
                'last_calculated_at' => now()
            ]
        );
    }

    protected function calculateCosts(AnalyticsExport $export): array
    {
        $processingTime = $export->completed_at->diffInMinutes($export->created_at);
        $storageDays = now()->diffInDays($export->created_at);
        $fileSizeMB = $export->file_size / 1024 / 1024; // Convert bytes to MB
        $bandwidthGB = $export->download_count * ($fileSizeMB / 1024); // Convert MB to GB

        return [
            'processing' => $processingTime * $this->costRates['processing'],
            'storage' => $storageDays * $fileSizeMB * $this->costRates['storage'],
            'bandwidth' => $bandwidthGB * $this->costRates['bandwidth'],
            'compute' => $processingTime * $export->compute_units * $this->costRates['compute']
        ];
    }

    /**
     * Get cost history for an export
     */
    public function getCostHistory(int $exportId)
    {
        return ExportCost::where('export_id', $exportId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Generate cost report for time period
     */
    public function generateCostReport(Carbon $startDate, Carbon $endDate, ?int $tenantId = null)
    {
        $query = ExportCost::whereBetween('created_at', [$startDate, $endDate])
            ->with('export');

        if ($tenantId) {
            $query->whereHas('export', function ($q) use ($tenantId) {
                $q->where('tenant_id', $tenantId);
            });
        }

        $costs = $query->get();

        return [
            'total_cost' => $costs->sum('total_cost'),
            'average_cost' => $costs->avg('total_cost'),
            'export_count' => $costs->count(),
            'cost_breakdown' => [
                'processing' => $costs->sum('processing_cost'),
                'storage' => $costs->sum('storage_cost'),
                'bandwidth' => $costs->sum('bandwidth_cost'),
                'compute' => $costs->sum('compute_cost')
            ],
            'cost_trend' => $this->calculateCostTrend($costs)
        ];
    }

    protected function calculateCostTrend($costs): array
    {
        return $costs->groupBy(function ($item) {
            return $item->created_at->format('Y-m-d');
        })->map(function ($dailyCosts) {
            return $dailyCosts->sum('total_cost');
        })->toArray();
    }

    /**
     * Compare actual vs budgeted costs
     */
    public function compareToBudget(int $budgetId, Carbon $startDate, Carbon $endDate): array
    {
        $actualCosts = $this->generateCostReport($startDate, $endDate);
        $budget = DB::table('export_budgets')->find($budgetId);

        return [
            'budget_amount' => $budget->amount,
            'actual_amount' => $actualCosts['total_cost'],
            'variance' => $budget->amount - $actualCosts['total_cost'],
            'variance_percentage' => ($budget->amount > 0) 
                ? (($budget->amount - $actualCosts['total_cost']) / $budget->amount) * 100 
                : 0,
            'period_start' => $startDate,
            'period_end' => $endDate,
            'cost_breakdown' => $actualCosts['cost_breakdown']
        ];
    }

    /**
     * Forecast future costs based on historical data
     */
    public function forecastCosts(int $daysToForecast, ?int $tenantId = null): array
    {
        $historyDays = min($daysToForecast * 3, 90); // Use up to 90 days history
        $startDate = now()->subDays($historyDays);
        $endDate = now();

        $historicalData = $this->generateCostReport($startDate, $endDate, $tenantId);
        $dailyAverage = $historicalData['total_cost'] / $historyDays;

        return [
            'forecast_period_days' => $daysToForecast,
            'forecast_total' => $dailyAverage * $daysToForecast,
            'daily_average' => $dailyAverage,
            'confidence_score' => $this->calculateConfidenceScore($historicalData),
            'historical_period' => [
                'days' => $historyDays,
                'total_cost' => $historicalData['total_cost']
            ]
        ];
    }

    protected function calculateConfidenceScore(array $historicalData): float
    {
        // Simple confidence calculation based on data volume
        $dataPoints = $historicalData['export_count'];
        return min(0.99, $dataPoints / 100); // Cap at 99% confidence
    }

    /**
     * Set custom cost rates
     */
    public function setCostRates(array $rates): void
    {
        $this->costRates = array_merge($this->costRates, $rates);
    }
}