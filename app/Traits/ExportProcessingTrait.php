<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait ExportProcessingTrait
{
    protected function getExportData(): array
    {
        return Cache::remember("export_data_{$this->scheduledExport->id}", 3600, function() {
            return match ($this->scheduledExport->template) {
                'analytics' => $this->getAnalyticsData(),
                'content_approval' => $this->getContentApprovalData(),
                'user_activity' => $this->getUserActivityData(),
                'ai_usage' => $this->getAIUsageData(),
                default => throw new \InvalidArgumentException("Unknown export template: {$this->scheduledExport->template}"),
            };
        });
    }

    protected function getAIUsageData(): array
    {
        return [
            'period' => $this->scheduledExport->period,
            'threshold' => config('analytics.ai_usage_threshold'),
            'users' => \App\Models\User::query()
                ->where('ai_usage_count', '>', 0)
                ->whereBetween('updated_at', $this->getDateRange())
                ->get()
                ->toArray()
        ];
    }

    protected function getDateRange(): array
    {
        return match ($this->scheduledExport->period) {
            'last_week' => [now()->subWeek(), now()],
            'last_month' => [now()->subMonth(), now()],
            'last_quarter' => [now()->subQuarter(), now()],
            default => [now()->subDay(), now()],
        };
    }

    protected function calculateNextRun()
    {
        return match ($this->scheduledExport->frequency) {
            'hourly' => now()->addHour(),
            'daily' => now()->addDay(),
            'weekly' => now()->addWeek(),
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addQuarter(),
            default => now()->addDay(),
        };
    }
}