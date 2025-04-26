<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\Log;

/**
 * Exports content approval analytics data to Excel with multiple sheets
 */
class ContentApprovalAnalyticsExport implements FromArray, WithMultipleSheets
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function sheets(): array
    {
        try {
            return [
                new SummarySheet($this->data),
                new StepCompletionSheet($this->data['step_completion_rates'] ?? []),
                new RejectionReasonsSheet($this->data['rejection_reasons']['breakdown'] ?? []),
                new ContentTypeMetricsSheet($this->data['content_type_metrics'] ?? [])
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate analytics export sheets', [
                'error' => $e->getMessage(),
                'data' => array_keys($this->data)
            ]);
            throw $e;
        }
    }
}

class SummarySheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        try {
            return [
                ['Total Approvals', $this->data['total_approvals'] ?? 0],
                ['Total Rejections', $this->data['total_rejections'] ?? 0],
                ['Approval Rate', $this->formatPercentage($this->data['approval_rate'] ?? 0)],
                ['Average Approval Time', $this->formatTime($this->data['average_approval_time'] ?? 0)],
                ['Efficiency Score', $this->formatDecimal($this->data['efficiency_score'] ?? 0)]
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate summary sheet', ['error' => $e->getMessage()]);
            return [];
        }
    }

    public function title(): string
    {
        return 'Summary';
    }

    public function headings(): array
    {
        return ['Metric', 'Value'];
    }

    protected function formatTime(float $seconds): string
    {
        if ($seconds <= 0) {
            return 'N/A';
        }
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }

    protected function formatPercentage(float $value): string
    {
        return number_format($value, 2) . '%';
    }

    protected function formatDecimal(float $value): string
    {
        return number_format($value, 2);
    }
}

class StepCompletionSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $step => $rate) {
            $rows[] = [$step, number_format($rate, 2) . '%'];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Step Completion';
    }

    public function headings(): array
    {
        return ['Step Name', 'Completion Rate (%)'];
    }
}

class RejectionReasonsSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $reason => $count) {
            $rows[] = [$reason, $count];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Rejection Reasons';
    }

    public function headings(): array
    {
        return ['Reason', 'Count'];
    }
}

class ContentTypeMetricsSheet implements FromArray, WithTitle, WithHeadings, ShouldAutoSize
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];
        foreach ($this->data as $type => $metrics) {
            $rows[] = [
                $type,
                $metrics['count'],
                number_format($metrics['approval_rate'], 2) . '%',
                $this->formatTime($metrics['avg_time'])
            ];
        }
        return $rows;
    }

    public function title(): string
    {
        return 'Content Types';
    }

    public function headings(): array
    {
        return ['Type', 'Count', 'Approval Rate (%)', 'Avg Approval Time'];
    }

    protected function formatTime($seconds)
    {
        if (!$seconds) return 'N/A';
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
