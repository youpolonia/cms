<?php

namespace App\Http\Livewire;

use App\Models\Content;
use Livewire\Component;
use Illuminate\Support\Facades\Bus;

class ContentList extends Component
{
    public $contents;
    public $selected = [];
    public $bulkAction = '';
    public $batchId;
    public $batchProgress = 0;

    protected $listeners = ['batchProgressUpdated'];

    public function mount()
    {
        $this->contents = Content::with('versions')->get();
    }

    public function updatedSelected()
    {
        $this->dispatch('selectedCountUpdated', count: count($this->selected));
    }

    public function performBulkAction()
    {
        $this->validate([
            'bulkAction' => 'required|in:publish,archive,delete',
            'selected' => 'required|array|min:1'
        ]);

        $contents = Content::whereIn('id', $this->selected)->get();
        $jobs = [];

        foreach ($contents as $content) {
            $jobs[] = new \App\Jobs\ProcessBulkContent(
                $content,
                $this->bulkAction
            );
        }

        $batch = Bus::batch($jobs)
            ->then(function () {
                $this->dispatch('bulkActionComplete');
            })
            ->catch(function () {
                $this->dispatch('bulkActionFailed');
            })
            ->finally(function () {
                $this->reset(['selected', 'bulkAction']);
            })
            ->dispatch();

        $this->batchId = $batch->id;
    }

    public function batchProgressUpdated($progress)
    {
        $this->batchProgress = $progress;
    }

    public function render()
    {
        return view('livewire.content-list', [
            'batchInProgress' => $this->batchProgress > 0 && $this->batchProgress < 100
        ]);
    }
}