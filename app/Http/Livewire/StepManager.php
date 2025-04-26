<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ErrorResolutionStep;
use App\Models\ErrorResolutionWorkflow;

class StepManager extends Component
{
    public ErrorResolutionWorkflow $workflow;
    public $steps = [];
    public $confirmingStepDeletion = false;
    public $stepIdBeingDeleted;

    protected $listeners = ['refreshSteps' => '$refresh'];

    public function mount(ErrorResolutionWorkflow $workflow)
    {
        $this->workflow = $workflow;
        $this->steps = $workflow->steps()->orderBy('order')->get()->toArray();
    }

    public function updateStepOrder($orderedIds)
    {
        foreach ($orderedIds as $order => $id) {
            ErrorResolutionStep::where('id', $id)
                ->update(['order' => $order + 1]);
        }

        $this->emit('refreshSteps');
    }

    public function confirmStepDeletion($stepId)
    {
        $this->confirmingStepDeletion = true;
        $this->stepIdBeingDeleted = $stepId;
    }

    public function deleteStep()
    {
        $step = ErrorResolutionStep::find($this->stepIdBeingDeleted);
        $step->delete();
        
        $this->confirmingStepDeletion = false;
        $this->emit('refreshSteps');
    }

    public function render()
    {
        return view('livewire.step-manager');
    }
}