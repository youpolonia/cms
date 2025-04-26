<?php

namespace App\View\Components;

use App\Models\ThemeVersionApproval;
use Illuminate\View\Component;

class ApprovalDashboardWidget extends Component
{
    public $pendingApprovals;
    public $approvalStats;
    public $hasUnreadNotifications;
    public $userPendingActions;
    public $workflowProgress;

    public function __construct()
    {
        $user = auth()->user();
        $analyticsService = app(ThemeApprovalAnalyticsService::class);
        
        $this->pendingApprovals = ThemeVersionApproval::with([
                'themeVersion.theme', 
                'workflowStep',
                'currentStep'
            ])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $this->approvalStats = [
            'pending' => ThemeVersionApproval::where('status', 'pending')->count(),
            'approved' => ThemeVersionApproval::where('status', 'approved')->count(),
            'rejected' => ThemeVersionApproval::where('status', 'rejected')->count(),
            'avgTime' => $analyticsService->getAverageApprovalTime(),
            'bottlenecks' => $analyticsService->getApprovalBottlenecks()
        ];

        $this->hasUnreadNotifications = $user->unreadNotifications()
            ->where('type', 'App\Notifications\ThemeApprovalNotification')
            ->exists();

        // Get pending actions for current user
        $this->userPendingActions = ThemeVersionApproval::whereHas('currentStep', function($query) use ($user) {
                $query->where('role_id', $user->role_id);
            })
            ->where('status', 'pending')
            ->count();

        // Enhanced workflow progress stats
        $progressData = $analyticsService->getWorkflowProgressMetrics();
        $this->workflowProgress = [
            'totalSteps' => ThemeApprovalStep::count(),
            'completedSteps' => ThemeVersionApproval::where('status', 'pending')
                ->avg('current_step_number') ?? 0,
            'avgStepTime' => $progressData['avg_step_time'],
            'slowestStep' => $progressData['slowest_step'],
            'completionRate' => $progressData['completion_rate']
        ];
    }

    public function render()
    {
        return view('components.approval-dashboard-widget');
    }
}
