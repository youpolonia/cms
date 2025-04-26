<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Content;
use App\Models\ContentVersion;
use App\Models\ApprovalWorkflow;
use App\Services\ContentApprovalService;

class ContentSubmission extends Component
{
    public $contentId;
    public $content;
    public $versionNotes = '';
    public $selectedWorkflowId;
    public $availableWorkflows = [];

    protected $rules = [
        'versionNotes' => 'required|string|max:500',
        'selectedWorkflowId' => 'required|exists:approval_workflows,id'
    ];

    public function mount($contentId)
    {
        $this->contentId = $contentId;
        $this->content = Content::findOrFail($contentId);
        $this->availableWorkflows = ApprovalWorkflow::where('content_type_id', $this->content->content_type_id)
            ->where('is_active', true)
            ->get();
    }

    public function submitForApproval()
    {
        $this->validate();

        $version = ContentVersion::create([
            'content_id' => $this->contentId,
            'content' => $this->content->latestVersion?->content ?? '',
            'version_notes' => $this->versionNotes,
            'is_autosave' => false
        ]);

        app(ContentApprovalService::class)->startApprovalProcess(
            $version,
            $this->selectedWorkflowId
        );

        session()->flash('message', 'Content submitted for approval successfully');
        return redirect()->route('content.show', $this->contentId);
    }

    public function render()
    {
        return view('livewire.content-submission');
    }
}