<?php

namespace App\Services;

use App\Models\ThemeApprovalWorkflow;
use App\Models\ThemeVersion;
use App\Models\ThemeApprovalStatistics;
use Illuminate\Support\Collection;

class ThemeApprovalAnalyticsService
{
    public function getWorkflowMetrics(ThemeApprovalWorkflow $workflow): array
    {
        $versions = $workflow->versions()->with('approvalStats')->get();
        
        return [
            'total_approvals' => $this->countTotalApprovals($versions),
            'total_rejections' => $this->countTotalRejections($versions),
            'average_approval_time' => $this->calculateAverageApprovalTime($versions),
            'approval_rate' => $this->calculateApprovalRate($versions),
            'step_completion_rates' => $this->calculateStepCompletionRates($workflow),
            'rejection_reasons' => $this->analyzeRejectionReasons($versions),
            'efficiency_score' => $this->calculateEfficiencyScore($versions)
        ];
    }

    protected function countTotalApprovals(Collection $versions): int
    {
        return $versions->sum(function($version) {
            return $version->approvalStats->sum('approvals_count');
        });
    }

    protected function countTotalRejections(Collection $versions): int
    {
        return $versions->sum(function($version) {
            return $version->approvalStats->sum('rejections_count');
        });
    }

    protected function calculateAverageApprovalTime(Collection $versions): ?float
    {
        $times = $versions->flatMap(function($version) {
            return $version->approvalStats->pluck('time_taken_seconds');
        })->filter();

        return $times->isNotEmpty() ? $times->avg() : null;
    }

    protected function calculateApprovalRate(Collection $versions): float
    {
        $total = $this->countTotalApprovals($versions) + $this->countTotalRejections($versions);
        return $total > 0 ? ($this->countTotalApprovals($versions) / $total) * 100 : 0;
    }

    protected function calculateStepCompletionRates(ThemeApprovalWorkflow $workflow): array
    {
        return $workflow->steps->mapWithKeys(function($step) {
            $stats = ThemeApprovalStatistics::where('approval_step_id', $step->id)->get();
            $completed = $stats->where('completion_percentage', 100)->count();
            
            return [
                $step->name => $stats->isNotEmpty() 
                    ? ($completed / $stats->count()) * 100 
                    : 0
            ];
        })->toArray();
    }

    protected function analyzeRejectionReasons(Collection $versions): array
    {
        $reasons = $versions->pluck('currentApproval.rejection_reason')
            ->filter()
            ->countBy()
            ->sortDesc();

        return [
            'total' => $reasons->sum(),
            'breakdown' => $reasons->toArray()
        ];
    }

    protected function calculateEfficiencyScore(Collection $versions): float
    {
        $totalSteps = $versions->count() * $versions->first()->approvalStats->count();
        $completedSteps = $versions->sum(function($version) {
            return $version->approvalStats->where('completion_percentage', 100)->count();
        });

        $avgTime = $this->calculateAverageApprovalTime($versions) ?? 1;
        $timeFactor = min(1, 86400 / $avgTime); // Normalize to 1 day max

        return ($completedSteps / $totalSteps) * $timeFactor * 100;
    }

    public function getVersionComparison(ThemeVersion $version1, ThemeVersion $version2): array
    {
        return [
            'approval_time_diff' => $this->compareApprovalTimes($version1, $version2),
            'approval_rate_diff' => $this->compareApprovalRates($version1, $version2),
            'rejection_reason_diff' => $this->compareRejectionReasons($version1, $version2)
        ];
    }

    protected function compareApprovalTimes(ThemeVersion $v1, ThemeVersion $v2): ?float
    {
        $time1 = $v1->approvalStats->avg('time_taken_seconds');
        $time2 = $v2->approvalStats->avg('time_taken_seconds');

        if ($time1 && $time2) {
            return $time2 - $time1;
        }
        return null;
    }

    protected function compareApprovalRates(ThemeVersion $v1, ThemeVersion $v2): ?float
    {
        $rate1 = $this->calculateApprovalRate(collect([$v1]));
        $rate2 = $this->calculateApprovalRate(collect([$v2]));

        return $rate2 - $rate1;
    }

    protected function compareRejectionReasons(ThemeVersion $v1, ThemeVersion $v2): array
    {
        $reasons1 = $v1->currentApproval->rejection_reason ?? null;
        $reasons2 = $v2->currentApproval->rejection_reason ?? null;

        return [
            'version1' => $reasons1,
            'version2' => $reasons2,
            'changed' => $reasons1 !== $reasons2
        ];
    }
}
