<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Toggle extends Component
{
    public $name;
    public $checked;

    public function __construct($name, $checked = false)
    {
        $this->name = $name;
        $this->checked = $checked;
    }

    public function render()
    {
        return view('components.toggle');
    }
}