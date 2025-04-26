<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\ContentVersion;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use App\Services\ContentApprovalAnalyticsService;

class ContentApprovalDashboardWidget extends Component
{
    public $approvalStats;
    public $workflowProgress;
    public $userPendingActions;
    public $pendingApprovals;
    public $hasUnreadNotifications;

    public function __construct(ContentApprovalAnalyticsService $analyticsService)
    {
        // Get approval statistics from service
        $this->approvalStats = $analyticsService->getApprovalStats();
        
        // Get workflow progress from service
        $this->workflowProgress = $analyticsService->getWorkflowProgress();

        // Get user's pending actions (simplified for demo)
        $this->userPendingActions = auth()->user()->pendingContentApprovals()->count();

        // Get pending approvals
        $this->pendingApprovals = ContentVersion::with(['content', 'currentStep'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Check for unread notifications
        $this->hasUnreadNotifications = auth()->user()->unreadNotifications()
            ->where('type', 'App\Notifications\ContentApprovalNotification')
            ->exists();
    }

    public function render()
    {
        return view('components.content-approval-dashboard-widget');
    }
}
