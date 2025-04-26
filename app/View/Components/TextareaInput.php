<?php

namespace App\View\Components;

use Illuminate\View\Component;

class TextareaInput extends Component
{
    public $name;
    public $label;
    public $placeholder;
    public $rows;
    public $value;

    public function __construct($name, $label = '', $placeholder = '', $rows = 3, $value = '')
    {
        $this->name = $name;
        $this->label = $label;
        $this->placeholder = $placeholder;
        $this->rows = $rows;
        $this->value = $value;
    }

    public function render()
    {
        return view('components.textarea-input');
    }
}