<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ContentType;
use App\Models\ApprovalWorkflow;
use App\Models\ApprovalStep;

class ApprovalWorkflowManager extends Component
{
    public $contentTypeId;
    public $workflows = [];
    public $steps = [];
    public $selectedWorkflow = null;
    public $showStepForm = false;
    public $newStep = [
        'name' => '',
        'description' => '',
        'order' => 0,
        'approvers' => [],
        'all_approvers_required' => false
    ];

    protected $rules = [
        'newStep.name' => 'required|string|max:255',
        'newStep.description' => 'nullable|string',
        'newStep.order' => 'required|integer|min:0',
        'newStep.approvers' => 'required|array',
        'newStep.all_approvers_required' => 'boolean'
    ];

    public function mount($contentTypeId)
    {
        $this->contentTypeId = $contentTypeId;
        $this->loadWorkflows();
    }

    public function loadWorkflows()
    {
        $this->workflows = ApprovalWorkflow::with('steps')
            ->where('content_type_id', $this->contentTypeId)
            ->get();
    }

    public function selectWorkflow($workflowId)
    {
        $this->selectedWorkflow = $this->workflows->firstWhere('id', $workflowId);
        $this->steps = $this->selectedWorkflow->steps ?? [];
        $this->showStepForm = false;
    }

    public function addStep()
    {
        $this->validate();

        ApprovalStep::create([
            'workflow_id' => $this->selectedWorkflow->id,
            ...$this->newStep
        ]);

        $this->resetStepForm();
        $this->loadWorkflows();
        $this->selectWorkflow($this->selectedWorkflow->id);
    }

    public function resetStepForm()
    {
        $this->newStep = [
            'name' => '',
            'description' => '',
            'order' => count($this->steps),
            'approvers' => [],
            'all_approvers_required' => false
        ];
        $this->showStepForm = false;
    }

    public function render()
    {
        return view('livewire.approval-workflow-manager', [
            'contentType' => ContentType::find($this->contentTypeId),
            'users' => \App\Models\User::all(),
            'roles' => \Spatie\Permission\Models\Role::all()
        ]);
    }
}