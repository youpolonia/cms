<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use App\Models\ApprovalDecision;
use App\Models\User;
use App\Notifications\ApprovalCompletedNotification;
use App\Notifications\ContentApprovalNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class ApprovalWorkflowService
{
    public function initiateApproval(Content $content, ?ApprovalWorkflow $workflow = null): void
    {
        $workflow = $workflow ?? $this->getDefaultWorkflow($content);

        DB::transaction(function () use ($content, $workflow) {
            $content->update([
                'approval_status' => 'pending',
                'approval_workflow_id' => $workflow->id,
                'current_approval_step_id' => $workflow->steps()->orderBy('order')->first()->id
            ]);

            $this->notifyApprovers($content);
        });
    }

    protected function getDefaultWorkflow(Content $content): ApprovalWorkflow
    {
        return ApprovalWorkflow::where('content_type_id', $content->content_type_id)
            ->where('is_default', true)
            ->firstOrFail();
    }

    public function approveStep(Content $content, User $approver, string $comment = null): void
    {
        DB::transaction(function () use ($content, $approver, $comment) {
            $currentStep = $content->currentApprovalStep;

            ApprovalDecision::create([
                'content_id' => $content->id,
                'approval_step_id' => $currentStep->id,
                'user_id' => $approver->id,
                'decision' => 'approved',
                'comment' => $comment
            ]);

            if ($this->isLastStep($content)) {
                $this->completeApproval($content);
            } else {
                $this->moveToNextStep($content);
            }
        });
    }

    public function rejectStep(Content $content, User $approver, string $comment): void
    {
        DB::transaction(function () use ($content, $approver, $comment) {
            $currentStep = $content->currentApprovalStep;

            ApprovalDecision::create([
                'content_id' => $content->id,
                'approval_step_id' => $currentStep->id,
                'user_id' => $approver->id,
                'decision' => 'rejected',
                'comment' => $comment
            ]);

            $content->update([
                'approval_status' => 'rejected',
                'current_approval_step_id' => null
            ]);

            $this->notifyCreator($content, $comment);
        });
    }

    public function requestChanges(Content $content, User $approver, string $comment): void
    {
        DB::transaction(function () use ($content, $approver, $comment) {
            $currentStep = $content->currentApprovalStep;

            ApprovalDecision::create([
                'content_id' => $content->id,
                'approval_step_id' => $currentStep->id,
                'user_id' => $approver->id,
                'decision' => 'changes_requested',
                'comment' => $comment
            ]);

            $content->update([
                'approval_status' => 'changes_requested',
                'current_approval_step_id' => null
            ]);

            $this->notifyCreator($content, $comment);
        });
    }

    protected function isLastStep(Content $content): bool
    {
        $currentStep = $content->currentApprovalStep;
        $workflow = $content->approvalWorkflow;

        return $workflow->steps()
            ->where('order', '>', $currentStep->order)
            ->doesntExist();
    }

    protected function moveToNextStep(Content $content): void
    {
        $currentStep = $content->currentApprovalStep;
        $workflow = $content->approvalWorkflow;

        $nextStep = $workflow->steps()
            ->where('order', '>', $currentStep->order)
            ->orderBy('order')
            ->first();

        $content->update([
            'current_approval_step_id' => $nextStep->id
        ]);

        $this->notifyApprovers($content);
    }

    protected function completeApproval(Content $content): void
    {
        $content->update([
            'approval_status' => 'approved',
            'current_approval_step_id' => null
        ]);

        $this->notifyCreator($content, 'Your content has been approved');
        $this->notifyApprovers($content, true);
    }

    protected function notifyApprovers(Content $content, bool $isComplete = false): void
    {
        if ($isComplete) {
            Notification::send(
                $content->approvalWorkflow->approvers,
                new ApprovalCompletedNotification($content)
            );
        } else {
            $approvers = $content->currentApprovalStep->approvers;
            Notification::send(
                $approvers,
                new ContentApprovalNotification($content)
            );
        }
    }

    protected function notifyCreator(Content $content, string $message): void
    {
        $content->creator->notify(
            new ContentApprovalNotification($content, $message)
        );
    }

    public function getApprovalHistory(Content $content)
    {
        return ApprovalDecision::with(['user', 'approvalStep'])
            ->where('content_id', $content->id)
            ->orderBy('created_at')
            ->get();
    }

    public function getPendingApprovals(User $user)
    {
        return Content::whereHas('currentApprovalStep', function($query) use ($user) {
                $query->whereHas('approvers', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                });
            })
            ->where('approval_status', 'pending')
            ->with(['currentVersion', 'creator'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function getApprovalStats(Content $content): array
    {
        $decisions = ApprovalDecision::where('content_id', $content->id)
            ->select('decision', DB::raw('count(*) as count'))
            ->groupBy('decision')
            ->pluck('count', 'decision');

        return [
            'total_decisions' => $decisions->sum(),
            'approved' => $decisions->get('approved', 0),
            'rejected' => $decisions->get('rejected', 0),
            'changes_requested' => $decisions->get('changes_requested', 0),
            'current_status' => $content->approval_status,
            'current_step' => $content->currentApprovalStep?->name,
            'workflow_name' => $content->approvalWorkflow?->name
        ];
    }

    public function canUserApprove(Content $content, User $user): bool
    {
        if ($content->approval_status !== 'pending') {
            return false;
        }

        return $content->currentApprovalStep->approvers()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function getAvailableWorkflows(Content $content)
    {
        return ApprovalWorkflow::where('content_type_id', $content->content_type_id)
            ->orWhere('content_type_id', null)
            ->orderBy('is_default', 'desc')
            ->get();
    }

    public function createWorkflow(array $data): ApprovalWorkflow
    {
        return DB::transaction(function () use ($data) {
            $workflow = ApprovalWorkflow::create([
                'name' => $data['name'],
                'description' => $data['description'],
                'content_type_id' => $data['content_type_id'] ?? null,
                'is_default' => $data['is_default'] ?? false
            ]);

            foreach ($data['steps'] as $stepData) {
                $step = $workflow->steps()->create([
                    'name' => $stepData['name'],
                    'description' => $stepData['description'],
                    'order' => $stepData['order']
                ]);

                $step->approvers()->sync($stepData['approvers']);
            }

            return $workflow;
        });
    }

    public function updateWorkflow(ApprovalWorkflow $workflow, array $data): ApprovalWorkflow
    {
        return DB::transaction(function () use ($workflow, $data) {
            $workflow->update([
                'name' => $data['name'],
                'description' => $data['description'],
                'content_type_id' => $data['content_type_id'] ?? null,
                'is_default' => $data['is_default'] ?? false
            ]);

            // Delete removed steps
            $workflow->steps()
                ->whereNotIn('id', collect($data['steps'])->pluck('id')->filter())
                ->delete();

            foreach ($data['steps'] as $stepData) {
                if (isset($stepData['id'])) {
                    $step = $workflow->steps()->findOrFail($stepData['id']);
                    $step->update([
                        'name' => $stepData['name'],
                        'description' => $stepData['description'],
                        'order' => $stepData['order']
                    ]);
                } else {
                    $step = $workflow->steps()->create([
                        'name' => $stepData['name'],
                        'description' => $stepData['description'],
                        'order' => $stepData['order']
                    ]);
                }

                $step->approvers()->sync($stepData['approvers']);
            }

            return $workflow->refresh();
        });
    }

    public function resetApproval(Content $content): void
    {
        DB::transaction(function () use ($content) {
            ApprovalDecision::where('content_id', $content->id)->delete();

            $content->update([
                'approval_status' => 'draft',
                'approval_workflow_id' => null,
                'current_approval_step_id' => null
            ]);
        });
    }

    public function getWorkflowProgress(Content $content): array
    {
        $steps = $content->approvalWorkflow->steps()->orderBy('order')->get();
        $decisions = ApprovalDecision::where('content_id', $content->id)
            ->get()
            ->groupBy('approval_step_id');

        return $steps->map(function ($step) use ($decisions, $content) {
            $stepDecisions = $decisions->get($step->id, collect());
            $isCurrent = $content->current_approval_step_id === $step->id;

            return [
                'step' => $step,
                'decisions' => $stepDecisions,
                'status' => $this->getStepStatus($stepDecisions, $isCurrent),
                'is_current' => $isCurrent
            ];
        })->toArray();
    }

    protected function getStepStatus($decisions, $isCurrent): string
    {
        if ($decisions->isEmpty()) {
            return $isCurrent ? 'pending' : 'not_started';
        }

        if ($decisions->contains('decision', 'rejected')) {
            return 'rejected';
        }

        if ($decisions->contains('decision', 'changes_requested')) {
            return 'changes_requested';
        }

        return 'approved';
    }
}
