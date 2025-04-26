<?php

namespace App\Http\Livewire;

use App\Models\ExportHistory;
use Livewire\Component;
use Livewire\WithPagination;

class ExportHistoryManager extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $sortField = 'started_at';
    public $sortDirection = 'desc';
    public $search = '';
    public $statusFilter = '';
    public $scheduleFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'scheduleFilter' => ['except' => ''],
        'sortField',
        'sortDirection'
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }
        $this->sortField = $field;
    }

    public function render()
    {
        return view('livewire.export-history-manager', [
            'history' => ExportHistory::query()
                ->when($this->search, function ($query) {
                    $query->whereHas('template', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
                    });
                })
                ->when($this->statusFilter, function ($query) {
                    $query->where('status', $this->statusFilter);
                })
                ->when($this->scheduleFilter, function ($query) {
                    $query->where('schedule_id', $this->scheduleFilter);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage)
        ]);
    }
}