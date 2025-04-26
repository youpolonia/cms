<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ButtonLink extends Component
{
    public $href;
    public $type;
    public $text;

    public function __construct($href = '#', $type = 'primary', $text = 'Button')
    {
        $this->href = $href;
        $this->type = $type;
        $this->text = $text;
    }

    public function render()
    {
        return view('components.button-link');
    }
}