<?php

namespace App\Services;

use App\Models\AnalyticsExport;
use App\Models\ExportSla;
use App\Notifications\SlaViolationNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class ExportSlaService
{
    protected $defaultSlas = [
        'standard' => [
            'processing_time' => 60, // minutes
            'data_freshness' => 1440, // minutes (24 hours)
            'availability' => 99.9 // percentage
        ],
        'priority' => [
            'processing_time' => 15,
            'data_freshness' => 60,
            'availability' => 99.99
        ]
    ];

    /**
     * Define SLA for an export
     */
    public function defineSla(int $exportId, string $slaType = 'standard'): ExportSla
    {
        return ExportSla::updateOrCreate(
            ['export_id' => $exportId],
            [
                'sla_type' => $slaType,
                'metrics' => $this->defaultSlas[$slaType],
                'active' => true
            ]
        );
    }

    /**
     * Check SLA compliance for an export
     */
    public function checkCompliance(int $exportId): array
    {
        $export = AnalyticsExport::findOrFail($exportId);
        $sla = ExportSla::where('export_id', $exportId)->firstOrFail();

        $results = [
            'processing_time' => $this->checkProcessingTime($export, $sla),
            'data_freshness' => $this->checkDataFreshness($export, $sla),
            'availability' => $this->checkAvailability($export, $sla)
        ];

        $this->handleViolations($export, $results);

        return $results;
    }

    protected function checkProcessingTime(AnalyticsExport $export, ExportSla $sla): array
    {
        $actualTime = $export->completed_at->diffInMinutes($export->created_at);
        $allowedTime = $sla->metrics['processing_time'];

        return [
            'metric' => 'processing_time',
            'value' => $actualTime,
            'threshold' => $allowedTime,
            'compliant' => $actualTime <= $allowedTime
        ];
    }

    protected function checkDataFreshness(AnalyticsExport $export, ExportSla $sla): array
    {
        $freshness = now()->diffInMinutes($export->data_refreshed_at);
        $allowedFreshness = $sla->metrics['data_freshness'];

        return [
            'metric' => 'data_freshness',
            'value' => $freshness,
            'threshold' => $allowedFreshness,
            'compliant' => $freshness <= $allowedFreshness
        ];
    }

    protected function checkAvailability(AnalyticsExport $export, ExportSla $sla): array
    {
        // Calculate availability percentage based on uptime/downtime logs
        $uptimePercentage = $export->uptime_percentage ?? 100;

        return [
            'metric' => 'availability',
            'value' => $uptimePercentage,
            'threshold' => $sla->metrics['availability'],
            'compliant' => $uptimePercentage >= $sla->metrics['availability']
        ];
    }

    protected function handleViolations(AnalyticsExport $export, array $results): void
    {
        $violations = array_filter($results, fn($metric) => !$metric['compliant']);

        if (!empty($violations)) {
            Notification::send(
                $export->owner,
                new SlaViolationNotification($export, $violations)
            );

            $this->logViolation($export->id, $violations);
        }
    }

    protected function logViolation(int $exportId, array $violations): void
    {
        ExportSla::where('export_id', $exportId)
            ->increment('violation_count');

        // Additional violation logging can be added here
    }

    /**
     * Get SLA metrics history for an export
     */
    public function getSlaHistory(int $exportId)
    {
        return ExportSla::where('export_id', $exportId)
            ->with('export')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Generate SLA performance report
     */
    public function generateSlaReport(array $exportIds = [])
    {
        $query = ExportSla::query();

        if (!empty($exportIds)) {
            $query->whereIn('export_id', $exportIds);
        }

        return $query->with('export')
            ->get()
            ->groupBy('sla_type')
            ->map(function ($slas, $type) {
                return [
                    'sla_type' => $type,
                    'total_exports' => $slas->count(),
                    'compliant_exports' => $slas->where('violation_count', 0)->count(),
                    'violation_rate' => $slas->avg('violation_count'),
                    'metrics' => $this->calculateAverageMetrics($slas)
                ];
            });
    }

    protected function calculateAverageMetrics($slas): array
    {
        return [
            'processing_time' => $slas->avg(function ($sla) {
                return $sla->export->processing_time_minutes;
            }),
            'data_freshness' => $slas->avg(function ($sla) {
                return $sla->export->data_freshness_minutes;
            }),
            'availability' => $slas->avg(function ($sla) {
                return $sla->export->uptime_percentage;
            })
        ];
    }
}