<?php

namespace App\Services;

use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;
use App\Models\ApprovalDecision;
use App\Notifications\ContentApprovalNotification;
use Illuminate\Support\Facades\DB;

class ContentApprovalService
{
    public function startApprovalProcess(Content $content, ContentVersion $version, int $workflowId): void
    {
        DB::transaction(function () use ($content, $version, $workflowId) {
            $workflow = ApprovalWorkflow::findOrFail($workflowId);
            $firstStep = $workflow->steps()->orderBy('order')->first();

            $content->update([
                'approval_workflow_id' => $workflowId,
                'approval_status' => 'pending',
                'current_approval_step_id' => $firstStep->id
            ]);

            $version->update([
                'approval_status' => 'pending'
            ]);

            $this->notifyApprovers($firstStep, $content, $version);
        });
    }

    public function submitForApproval(Content $content, ContentVersion $version): void
    {
        $workflow = $content->type->approvalWorkflow;

        if (!$workflow) {
            throw new \Exception('No approval workflow defined for this content type');
        }

        $this->startApprovalProcess($content, $version, $workflow->id);
    }

    public function approveStep(Content $content, ContentVersion $version, int $userId, string $comment = null): void
    {
        DB::transaction(function () use ($content, $version, $userId, $comment) {
            $currentStep = $content->currentApprovalStep;
            $workflow = $content->approvalWorkflow;

            ApprovalDecision::create([
                'content_id' => $content->id,
                'content_version_id' => $version->id,
                'approval_step_id' => $currentStep->id,
                'user_id' => $userId,
                'decision' => 'approved',
                'comments' => $comment,
                'decision_at' => now()
            ]);

            $nextStep = $workflow->steps()
                ->where('order', '>', $currentStep->order)
                ->orderBy('order')
                ->first();

            if ($nextStep) {
                $content->update([
                    'current_approval_step_id' => $nextStep->id
                ]);

                $this->notifyApprovers($nextStep, $content, $version);
            } else {
                $this->completeApproval($content, $version);
            }
        });
    }

    public function rejectStep(Content $content, ContentVersion $version, int $userId, string $comment = null): void
    {
        DB::transaction(function () use ($content, $version, $userId, $comment) {
            $currentStep = $content->currentApprovalStep;

            ApprovalDecision::create([
                'content_id' => $content->id,
                'content_version_id' => $version->id,
                'approval_step_id' => $currentStep->id,
                'user_id' => $userId,
                'decision' => 'rejected',
                'comments' => $comment,
                'decision_at' => now()
            ]);

            $content->update([
                'approval_status' => 'rejected',
                'current_approval_step_id' => null
            ]);

            $version->update([
                'approval_status' => 'rejected'
            ]);

            $this->notifyCreator($content, $version, $comment);
        });
    }

    protected function completeApproval(Content $content, ContentVersion $version): void
    {
        $content->update([
            'approval_status' => 'approved',
            'current_approval_step_id' => null,
            'current_version_id' => $version->id,
            'status' => 'published',
            'published_at' => now()
        ]);

        $version->update([
            'approval_status' => 'approved',
            'is_approved' => true,
            'approved_at' => now()
        ]);

        $this->notifyCreator($content, $version);
    }

    public function getApprovalHistory(Content $content): array
    {
        return [
            'workflow' => $content->approvalWorkflow,
            'current_step' => $content->currentApprovalStep,
            'decisions' => $content->approvalDecisions()->with(['user', 'approvalStep'])->get(),
            'status' => $content->approval_status,
            'version' => $content->versions()->where('approval_status', '!=', 'pending')->first()
        ];
    }

    public function getPendingApprovals(int $userId, int $limit = 10)
    {
        return Content::whereHas('currentApprovalStep', function($query) use ($userId) {
                $query->whereHas('approvers', function($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
            })
            ->where('approval_status', 'pending')
            ->with(['currentApprovalStep', 'creator', 'type'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getApprovalStats(int $userId = null): array
    {
        $query = ApprovalDecision::query();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $stats = $query->select(
            DB::raw('count(*) as total'),
            DB::raw("sum(case when decision = 'approved' then 1 else 0 end) as approved"),
            DB::raw("sum(case when decision = 'rejected' then 1 else 0 end) as rejected"),
            DB::raw("avg(TIMESTAMPDIFF(HOUR, created_at, decision_at)) as avg_decision_time")
        )->first();

        return [
            'total_decisions' => $stats->total,
            'approved' => $stats->approved,
            'rejected' => $stats->rejected,
            'approval_rate' => $stats->total > 0 ? round(($stats->approved / $stats->total) * 100, 2) : 0,
            'avg_decision_time_hours' => round($stats->avg_decision_time, 2)
        ];
    }

    public function getContentRequiringApproval(): array
    {
        return [
            'pending' => Content::where('approval_status', 'pending')
                ->with(['currentApprovalStep', 'creator', 'type'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get(),
            'recently_approved' => Content::where('approval_status', 'approved')
                ->where('published_at', '>=', now()->subDays(7))
                ->with(['creator', 'type'])
                ->orderBy('published_at', 'desc')
                ->limit(10)
                ->get(),
            'recently_rejected' => Content::where('approval_status', 'rejected')
                ->where('updated_at', '>=', now()->subDays(7))
                ->with(['creator', 'type'])
                ->orderBy('updated_at', 'desc')
                ->limit(10)
                ->get()
        ];
    }

    protected function notifyApprovers(ApprovalStep $step, Content $content, ContentVersion $version): void
    {
        foreach ($step->approvers as $approver) {
            $approver->notify(new ContentApprovalNotification(
                $content,
                $version,
                $step,
                'approval_required'
            ));
        }
    }

    protected function notifyCreator(Content $content, ContentVersion $version, string $comment = null): void
    {
        $content->creator->notify(new ContentApprovalNotification(
            $content,
            $version,
            $content->currentApprovalStep,
            $content->approval_status === 'approved' ? 'approved' : 'rejected',
            $comment
        ));
    }

    public function canUserApprove(Content $content, int $userId): bool
    {
        if (!$content->currentApprovalStep) {
            return false;
        }

        return $content->currentApprovalStep->approvers()
            ->where('user_id', $userId)
            ->exists();
    }

    public function getApprovalWorkflowOptions(): array
    {
        return ApprovalWorkflow::with(['steps'])
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($workflow) {
                return [$workflow->id => $workflow->name . ' (' . $workflow->steps->count() . ' steps)'];
            })
            ->toArray();
    }

    public function getContentApprovalSummary(Content $content): array
    {
        $steps = $content->approvalWorkflow->steps()->orderBy('order')->get();
        $decisions = $content->approvalDecisions()->with(['user'])->get()->groupBy('approval_step_id');

        return [
            'content' => $content,
            'version' => $content->versions()->where('approval_status', '!=', 'pending')->first(),
            'steps' => $steps->map(function ($step) use ($decisions) {
                $stepDecisions = $decisions->get($step->id, collect());

                return [
                    'step' => $step,
                    'decisions' => $stepDecisions,
                    'status' => $stepDecisions->isEmpty() ? 'pending' : 
                               ($stepDecisions->contains('decision', 'rejected') ? 'rejected' : 'approved'),
                    'decision_made_at' => $stepDecisions->isNotEmpty() ? $stepDecisions->first()->decision_at : null
                ];
            }),
            'current_status' => $content->approval_status,
            'current_step' => $content->currentApprovalStep
        ];
    }
}