<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\AnalyticsExport;
use App\Services\ExportTagService;

class ExportTagManager extends Component
{
    public AnalyticsExport $export;
    public string $search = '';
    public array $selectedTags = [];
    public string $newTagName = '';
    public string $newTagColor = '#3b82f6';

    protected $listeners = ['refreshTags' => '$refresh'];

    public function mount(AnalyticsExport $export)
    {
        $this->export = $export;
        $this->selectedTags = $export->tags->pluck('id')->toArray();
    }

    public function render(ExportTagService $tagService)
    {
        return view('livewire.export-tag-manager', [
            'tags' => $tagService->getAllTagsWithCounts(),
            'searchResults' => $this->search 
                ? $tagService->searchTags($this->search)
                : collect()
        ]);
    }

    public function updatedSelectedTags()
    {
        $this->export->tags()->sync($this->selectedTags);
        $this->emit('tagsUpdated');
    }

    public function addNewTag(ExportTagService $tagService)
    {
        $this->validate([
            'newTagName' => 'required|string|max:255',
            'newTagColor' => 'required|string|size:7|starts_with:#'
        ]);

        $tag = $tagService->findOrCreateTag(
            $this->newTagName,
            $this->newTagColor
        );

        $this->selectedTags[] = $tag->id;
        $this->newTagName = '';
        $this->emit('refreshTags');
    }
}