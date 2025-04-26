<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\NotificationFilter;
use Illuminate\Support\Facades\Auth;

class FilterManager extends Component
{
    public $filters = [];
    public $newFilter = [
        'name' => '',
        'type' => 'category',
        'value' => '',
        'is_active' => true
    ];

    protected $rules = [
        'filters.*.name' => 'required|string|max:255',
        'filters.*.type' => 'required|in:category,priority,status,date_range',
        'filters.*.value' => 'required',
        'filters.*.is_active' => 'boolean',
        'newFilter.name' => 'required|string|max:255',
        'newFilter.type' => 'required|in:category,priority,status,date_range',
        'newFilter.value' => 'required',
        'newFilter.is_active' => 'boolean'
    ];

    public function mount()
    {
        $this->filters = Auth::user()
            ->notificationFilters()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function addFilter()
    {
        $this->validateOnly('newFilter');

        $filter = Auth::user()->notificationFilters()->create($this->newFilter);

        $this->filters[] = $filter->toArray();
        $this->reset('newFilter');
        $this->dispatch('filter-added');
    }

    public function updateFilter($index)
    {
        $this->validate([
            "filters.$index.name" => 'required|string|max:255',
            "filters.$index.type" => 'required|in:category,priority,status,date_range',
            "filters.$index.value" => 'required',
            "filters.$index.is_active" => 'boolean'
        ]);

        $filterData = $this->filters[$index];
        $filter = Auth::user()->notificationFilters()->find($filterData['id']);
        $filter->update($filterData);

        $this->dispatch('filter-updated');
    }

    public function deleteFilter($index)
    {
        $filter = Auth::user()->notificationFilters()->find($this->filters[$index]['id']);
        $filter->delete();
        unset($this->filters[$index]);
        $this->filters = array_values($this->filters);

        $this->dispatch('filter-deleted');
    }

    public function render()
    {
        return view('livewire.filter-manager');
    }
}