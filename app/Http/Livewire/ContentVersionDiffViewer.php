<?php

namespace App\Http\Livewire;

use App\Models\ContentVersionDiff;
use Livewire\Component;

class ContentVersionDiffViewer extends Component
{
    public $diff;
    public $activeChange = 0;
    public $showSideBySide = true;

    protected $listeners = ['changeSelected' => 'selectChange'];

    public function mount(ContentVersionDiff $diff)
    {
        $this->diff = $diff;
    }

    public function selectChange($index)
    {
        $this->activeChange = $index;
    }

    public function toggleViewMode()
    {
        $this->showSideBySide = !$this->showSideBySide;
    }

    public function render()
    {
        return view('livewire.content-version-diff-viewer', [
            'changes' => $this->diff->diff_data['changes'] ?? [],
            'summary' => $this->diff->summary,
            'fromVersion' => $this->diff->fromVersion,
            'toVersion' => $this->diff->toVersion
        ]);
    }
}