<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\AutopilotTask;
use App\Services\AutopilotService;

class AutopilotTaskManager extends Component
{
    public $selectedTask;
    public $showModal = false;

    protected $listeners = [
        'showTaskDetails' => 'showTaskDetails',
        'refreshTasks' => 'refreshTasks',
        'showModal' => 'showModal',
        'closeModal' => 'closeModal'
    ];

    public function showTaskDetails($taskId)
    {
        $this->selectedTask = AutopilotTask::findOrFail($taskId);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->selectedTask = null;
    }

    public function refreshTasks($status = null)
    {
        if ($this->selectedTask) {
            $this->selectedTask = AutopilotTask::find($this->selectedTask->id);
        }
    }

    public function retryTask($taskId)
    {
        $autopilot = app(AutopilotService::class);
        $autopilot->retryTask($taskId);
        $this->dispatch('taskUpdated');
        $this->dispatch('notify', message: 'Task queued for retry');
    }

    public function showModal($taskId = null)
    {
        if ($taskId) {
            $this->showTaskDetails($taskId);
        } else {
            $this->showModal = true;
        }
    }

    public function render()
    {
        return view('livewire.autopilot-task-manager');
    }
}