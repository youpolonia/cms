<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\ModerationQueue;
use Illuminate\Support\Facades\Auth;

class ApprovalDashboard extends Component
{
    public $perPage = 10;
    public $filter = 'all'; // all, assigned, completed

    protected $queryString = [
        'perPage' => ['except' => 10],
        'filter' => ['except' => 'all']
    ];

    public function render()
    {
        $query = ModerationQueue::with([
            'contentVersion.content',
            'currentStep',
            'decisions.user'
        ])->latest();

        if ($this->filter === 'assigned') {
            $query->whereHas('currentStep.approvers', function($q) {
                $q->where('user_id', Auth::id());
            });
        } elseif ($this->filter === 'completed') {
            $query->whereHas('decisions', function($q) {
                $q->where('user_id', Auth::id());
            });
        }

        return view('livewire.approval-dashboard', [
            'items' => $query->paginate($this->perPage)
        ]);
    }

    public function updateFilter($filter)
    {
        $this->filter = $filter;
        $this->resetPage();
    }
}