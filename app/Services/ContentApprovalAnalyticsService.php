<?php

namespace App\Services;

use App\Models\ContentVersion;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use Illuminate\Support\Facades\Cache;

class ContentApprovalAnalyticsService
{
    const CACHE_TYPE = 'approval_analytics';
    const CACHE_KEY_PREFIX = 'content_approval:';

    public function getApprovalStats(): array
    {
        return Cache::store('analytics')
            ->remember(
                self::CACHE_KEY_PREFIX.'stats', 
                $this->getCacheTtl(),
                function() {
            return [
                'pending' => ContentVersion::where('status', 'pending')->count(),
                'approved' => ContentVersion::where('status', 'approved')->count(),
                'rejected' => ContentVersion::where('status', 'rejected')->count()
            ];
        });
    }

    public function getWorkflowProgress(): array
    {
        return Cache::store('analytics')
            ->remember(
                self::CACHE_KEY_PREFIX.'workflow_progress', 
                $this->getCacheTtl(),
                function() {
                    $totalSteps = ApprovalWorkflow::count();
                    $completedSteps = ApprovalStep::where('is_completed', true)->count();
                    
                    $steps = ApprovalStep::with('workflow')
                        ->whereNotNull('completed_at')
                        ->get();
                        
                    $avgStepTime = $steps->avg(function($step) {
                        return $step->completed_at->diffInMinutes($step->created_at);
                    });
                    
                    $slowestStep = $steps->sortByDesc(function($step) {
                        return $step->completed_at->diffInMinutes($step->created_at);
                    })->first();
                    
                    $bottlenecks = $steps->filter(function($step) use ($avgStepTime) {
                        return $step->completed_at->diffInMinutes($step->created_at) > ($avgStepTime * 2);
                    })->map(function($step) {
                        return [
                            'step' => $step->name,
                            'avg_time' => $step->completed_at->diffInMinutes($step->created_at) . ' mins'
                        ];
                    })->toArray();

                    return [
                        'totalSteps' => $totalSteps,
                        'completedSteps' => $completedSteps,
                        'avgStepTime' => $avgStepTime ? round($avgStepTime) . ' mins' : 'N/A',
                        'slowestStep' => $slowestStep ? $slowestStep->name . ' (' . 
                            $slowestStep->completed_at->diffInMinutes($slowestStep->created_at) . ' mins)' : 'N/A',
                        'completionRate' => $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0,
                        'bottlenecks' => $bottlenecks
                    ];
                }
            );
    }

    public function getPendingApprovals(int $limit = 5)
    {
        return Cache::store('analytics')
            ->remember(
                self::CACHE_KEY_PREFIX.'pending_approvals:'.$limit,
                $this->getCacheTtl(),
                function() use ($limit) {
                    return ContentVersion::with(['content', 'currentStep'])
                        ->where('status', 'pending')
                        ->orderBy('created_at', 'desc')
                        ->paginate($limit);
                }
            );
    }

    protected function getCacheTtl(): int
    {
        $ttl = config('cache.stores.analytics.types.'.self::CACHE_TYPE.'.ttl', 3600);
        return config('cache.stores.analytics.validate_ttl')(self::CACHE_TYPE, $ttl);
    }

    public function clearCache(): void
    {
        Cache::store('analytics')->forget(self::CACHE_KEY_PREFIX.'stats');
        Cache::store('analytics')->forget(self::CACHE_KEY_PREFIX.'workflow_progress');
        Cache::store('analytics')->forget(self::CACHE_KEY_PREFIX.'pending_approvals:*');
        
        try {
            \MCP::useTool('cms-knowledge-server', 'cache_file', [
                'path' => self::CACHE_KEY_PREFIX.'stats',
                'data' => null
            ]);
            \MCP::useTool('cms-knowledge-server', 'cache_file', [
                'path' => self::CACHE_KEY_PREFIX.'workflow_progress', 
                'data' => null
            ]);
        } catch (\Exception $e) {
            \Log::warning("Failed to clear MCP cache: " . $e->getMessage());
        }
    }

    public function clearPendingApprovalsCache(int $limit): void
    {
        Cache::store('analytics')->forget(self::CACHE_KEY_PREFIX.'pending_approvals:'.$limit);
    }
}
