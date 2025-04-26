<?php

namespace App\View\Components;

use Illuminate\View\Component;

class SelectInput extends Component
{
    public $name;
    public $label;
    public $options;
    public $selected;
    public $placeholder;

    public function __construct($name, $label = '', $options = [], $selected = null, $placeholder = 'Select an option')
    {
        $this->name = $name;
        $this->label = $label;
        $this->options = $options;
        $this->selected = $selected;
        $this->placeholder = $placeholder;
    }

    public function render()
    {
        return view('components.select-input');
    }
}