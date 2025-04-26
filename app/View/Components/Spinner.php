<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Spinner extends Component
{
    public $size;
    public $color;

    public function __construct($size = 'md', $color = 'primary')
    {
        $this->size = $size;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.spinner');
    }
}