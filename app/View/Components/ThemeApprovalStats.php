<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ThemeApprovalStats extends Component
{
    public $approvals;
    public $required;
    public $completion;
    public $rejections;
    public $timeTaken;
    public $approvalLogic;
    public $approvalThreshold;
    public $averageApprovalTime;
    public $historicalApprovalRate;
    public $previousApprovalTime;

    public function __construct(
        $approvals,
        $required,
        $completion,
        $rejections,
        $timeTaken,
        $approvalLogic,
        $approvalThreshold,
        $averageApprovalTime,
        $historicalApprovalRate,
        $previousApprovalTime
    ) {
        $this->approvals = $approvals;
        $this->required = $required;
        $this->completion = $completion;
        $this->rejections = $rejections;
        $this->timeTaken = $timeTaken;
        $this->approvalLogic = $approvalLogic;
        $this->approvalThreshold = $approvalThreshold;
        $this->averageApprovalTime = $averageApprovalTime;
        $this->historicalApprovalRate = $historicalApprovalRate;
        $this->previousApprovalTime = $previousApprovalTime;
    }

    public function render()
    {
        return view('components.theme-approval-stats');
    }
}
