<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AnalyticsExport;
use Illuminate\Support\Facades\Auth;

class ExportHistory extends Component
{
    use WithPagination;

    public $perPage = 10;
    public $search = '';
    public $status = '';
    public $type = '';
    public $dateFrom = '';
    public $dateTo = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'status' => ['except' => ''],
        'type' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
    ];

    public function render()
    {
        $exports = AnalyticsExport::query()
            ->where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->type, function ($query) {
                $query->where('export_type', $this->type);
            })
            ->when($this->dateFrom, function ($query) {
                $query->where('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($query) {
                $query->where('created_at', '<=', $this->dateTo);
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.export-history', [
            'exports' => $exports,
            'statusOptions' => [
                '' => 'All Statuses',
                'pending' => 'Pending',
                'processing' => 'Processing',
                'completed' => 'Completed',
                'failed' => 'Failed',
            ],
            'typeOptions' => [
                '' => 'All Types',
                'analytics' => 'Analytics',
                'content' => 'Content',
                'users' => 'Users',
            ],
        ]);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status', 'type', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }
}