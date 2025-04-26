<?php

namespace App\View\Components;

use Illuminate\View\Component;

class CheckboxInput extends Component
{
    public $name;
    public $label;
    public $checked;
    public $value;

    public function __construct($name, $label = '', $checked = false, $value = '1')
    {
        $this->name = $name;
        $this->label = $label;
        $this->checked = $checked;
        $this->value = $value;
    }

    public function render()
    {
        return view('components.checkbox-input');
    }
}