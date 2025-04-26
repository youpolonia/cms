<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\AnalyticsExport;
use App\Services\DataQualityService;
use Carbon\Carbon;

class DataQualityDashboard extends Component
{
    public $exportId;
    public $timeRange = '7d';
    public $metrics = [];
    public $trendData = [];
    public $anomalies = [];
    public $loading = true;

    protected $listeners = ['timeRangeChanged' => 'setTimeRange'];

    public function mount($exportId)
    {
        $this->exportId = $exportId;
        $this->loadData();
    }

    public function setTimeRange($range)
    {
        $this->timeRange = $range;
        $this->loadData();
    }

    public function loadData()
    {
        $this->loading = true;
        $this->metrics = $this->getQualityMetrics();
        $this->trendData = $this->getTrendData();
        $this->anomalies = $this->detectAnomalies();
        $this->loading = false;
    }

    protected function getQualityMetrics(): array
    {
        $export = AnalyticsExport::findOrFail($this->exportId);
        
        return [
            'completeness' => $export->completeness_score ?? 0,
            'accuracy' => $export->accuracy_score ?? 0,
            'consistency' => $export->consistency_score ?? 0,
            'timeliness' => $export->timeliness_score ?? 0,
            'validity' => $export->validity_score ?? 0,
            'uniqueness' => $export->uniqueness_score ?? 0,
            'overall' => $export->quality_score ?? 0
        ];
    }

    protected function getTrendData(): array
    {
        $days = match($this->timeRange) {
            '1d' => 1,
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 7
        };

        return AnalyticsExport::where('id', $this->exportId)
            ->orWhere('parent_export_id', $this->exportId)
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->orderBy('created_at')
            ->get()
            ->map(function ($export) {
                return [
                    'date' => $export->created_at->format('Y-m-d'),
                    'completeness' => $export->completeness_score,
                    'accuracy' => $export->accuracy_score,
                    'consistency' => $export->consistency_score,
                    'timeliness' => $export->timeliness_score,
                    'validity' => $export->validity_score,
                    'uniqueness' => $export->uniqueness_score,
                    'overall' => $export->quality_score
                ];
            })
            ->toArray();
    }

    protected function detectAnomalies(): array
    {
        if (empty($this->trendData)) {
            return [];
        }

        $threshold = 0.2; // 20% change considered anomaly
        $anomalies = [];
        $lastValues = end($this->trendData);

        foreach ($lastValues as $metric => $value) {
            if ($metric === 'date') continue;

            $values = array_column($this->trendData, $metric);
            $avg = array_sum($values) / count($values);
            
            if (abs($value - $avg) / $avg > $threshold) {
                $anomalies[$metric] = [
                    'current' => $value,
                    'average' => $avg,
                    'deviation' => round(abs($value - $avg) / $avg * 100, 2)
                ];
            }
        }

        return $anomalies;
    }

    public function render()
    {
        return view('livewire.data-quality-dashboard');
    }
}