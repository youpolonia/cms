<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Services\AutopilotService;
use App\Models\AutopilotTask;

class AutopilotQueueStatus extends Component
{
    public $pendingCount;
    public $processingCount;
    public $completedCount;
    public $failedCount;
    public $filterStatus = 'all';

    protected $listeners = [
        'taskUpdated' => 'updateCounts',
        'refreshCounts' => 'updateCounts'
    ];

    public function mount()
    {
        $this->updateCounts();
    }

    public function pollCounts()
    {
        $this->updateCounts();
    }

    public function updateCounts()
    {
        $this->pendingCount = DB::table('autopilot_tasks')
            ->where('status', 'pending')
            ->count();

        $this->processingCount = DB::table('autopilot_tasks')
            ->where('status', 'processing')
            ->count();

        $this->completedCount = DB::table('autopilot_tasks')
            ->where('status', 'completed')
            ->count();

        $this->failedCount = DB::table('autopilot_tasks')
            ->where('status', 'failed')
            ->count();
    }

    public function updatedFilterStatus()
    {
        $this->dispatch('filterChanged', status: $this->filterStatus);
        $this->dispatch('refreshTasks', status: $this->filterStatus);
    }

    public function applyFilter($status)
    {
        $this->filterStatus = $status;
        $this->dispatch('refreshTasks', status: $status);
    }

    public function retryFailedTasks()
    {
        $autopilot = app(AutopilotService::class);
        $autopilot->retryFailedTasks();
        $this->updateCounts();
        $this->dispatch('notify', message: 'Failed tasks queued for retry');
    }

    public function viewTask($taskId)
    {
        $this->dispatch('showTaskDetails', taskId: $taskId)
            ->self();
        $this->dispatch('showTaskDetails', taskId: $taskId)
            ->to('autopilot-task-manager');
    }

    public function render()
    {
        return view('livewire.autopilot-queue-status');
    }
}