<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ModerationQueue;
use App\Models\ApprovalDecision;
use App\Services\ContentApprovalService;

class ApprovalDecisionForm extends Component
{
    public $queueId;
    public $moderationItem;
    public $decision = 'approve';
    public $comments = '';
    public $isProcessing = false;

    protected $rules = [
        'decision' => 'required|in:approve,reject',
        'comments' => 'nullable|string|max:500'
    ];

    public function mount($queueId)
    {
        $this->queueId = $queueId;
        $this->moderationItem = ModerationQueue::with([
            'contentVersion.content',
            'currentStep'
        ])->findOrFail($queueId);
    }

    public function submitDecision()
    {
        $this->validate();
        $this->isProcessing = true;

        $decision = ApprovalDecision::create([
            'content_version_id' => $this->moderationItem->content_version_id,
            'step_id' => $this->moderationItem->current_step_id,
            'user_id' => auth()->id(),
            'decision' => $this->decision,
            'comments' => $this->comments
        ]);

        app(ContentApprovalService::class)->processApprovalDecision($decision);

        session()->flash('message', 'Decision submitted successfully');
        return redirect()->route('approvals.pending');
    }

    public function render()
    {
        return view('livewire.approval-decision-form', [
            'content' => $this->moderationItem->contentVersion->content,
            'version' => $this->moderationItem->contentVersion,
            'step' => $this->moderationItem->currentStep
        ]);
    }
}