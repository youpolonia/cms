<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PageBuilder extends Component
{
    public ?int $content_id;

    public function mount(?int $content_id = null) 
    {
        $this->content_id = $content_id;
    }

    public function render()
    {
        return view('livewire.page-builder');
    }
}