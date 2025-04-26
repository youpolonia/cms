<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Models\ThemeVersionApproval;

class ApprovalTimeline extends Component
{
    public $approvals;
    public $currentStep;
    public $totalSteps;
    public $progress;

    public function __construct($version)
    {
        $this->approvals = $version->approvals()
            ->with('approver')
            ->orderBy('created_at')
            ->get();

        $this->currentStep = $version->current_approval_step?->order;
        $this->totalSteps = $version->approvalWorkflow?->steps->count();
        
        // Calculate overall progress
        $completed = $this->approvals->where('status', 'approved')->count();
        $total = $this->totalSteps ?: 1; // Avoid division by zero
        $this->progress = [
            'completed' => $completed,
            'total' => $total,
            'percentage' => ($completed / $total) * 100
        ];
    }

    public function render()
    {
        return view('components.approval-timeline');
    }
}
