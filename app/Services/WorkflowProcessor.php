<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalDecision;
use App\Notifications\ContentApprovalNotification;
use Illuminate\Support\Facades\Notification;

class WorkflowProcessor
{
    public function initiateApproval(Content $content): void
    {
        $workflow = $this->getApplicableWorkflow($content);
        
        if (!$workflow) {
            $content->update(['status' => 'auto_approved']);
            return;
        }

        $content->update([
            'status' => 'pending_approval',
            'current_approval_step_id' => $workflow->steps()->first()->id
        ]);

        $this->notifyApprovers($content);
    }

    public function processDecision(ApprovalDecision $decision): void
    {
        $content = $decision->content;
        $currentStep = $content->currentApprovalStep;
        $workflow = $currentStep->workflow;

        if ($decision->isRejected()) {
            $content->update(['status' => 'rejected']);
            $this->notifyRejection($content);
            return;
        }

        $nextStep = $workflow->steps()
            ->where('order', '>', $currentStep->order)
            ->first();

        if (!$nextStep) {
            $content->update(['status' => 'approved']);
            $this->notifyFinalApproval($content);
            return;
        }

        $content->update(['current_approval_step_id' => $nextStep->id]);
        $this->notifyApprovers($content);
    }

    protected function getApplicableWorkflow(Content $content): ?ApprovalWorkflow
    {
        return ApprovalWorkflow::where('is_active', true)
            ->get()
            ->first(fn ($workflow) => $workflow->isApplicableTo($content->type));
    }

    protected function notifyApprovers(Content $content): void
    {
        $approvers = $content->currentApprovalStep->approvers;
        Notification::send($approvers, new ContentApprovalNotification($content));
    }

    protected function notifyRejection(Content $content): void
    {
        // Implementation for rejection notifications
    }

    protected function notifyFinalApproval(Content $content): void
    {
        // Implementation for final approval notifications
    }
}