<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Caxy\HtmlDiff\HtmlDiff;

class ContentVersionComparison extends Component
{
    public $content;
    public $version1;
    public $version2;
    public $diffResult;
    public $metadataDiff = [];

    public function mount($content)
    {
        $this->content = $content;
        $this->version1 = $content->versions()->latest()->first();
        $this->version2 = $content->versions()->latest()->skip(1)->first();
    }

    public function compare()
    {
        // Compare content
        $htmlDiff = new HtmlDiff($this->version1->content, $this->version2->content);
        $this->diffResult = $htmlDiff->build();

        // Compare metadata
        $this->metadataDiff = [
            'author' => $this->version1->user_id !== $this->version2->user_id,
            'created_at' => $this->version1->created_at->ne($this->version2->created_at),
            'updated_at' => $this->version1->updated_at->ne($this->version2->updated_at),
        ];
    }

    public function render()
    {
        return view('livewire.content-version-comparison');
    }
}
