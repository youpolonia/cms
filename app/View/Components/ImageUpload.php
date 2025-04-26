<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ImageUpload extends Component
{
    public $name;
    public $value;
    public $label;
    public $required;
    public $accept;

    public function __construct(
        string $name,
        $value = null,
        string $label = 'Image',
        bool $required = false,
        string $accept = 'image/*'
    ) {
        $this->name = $name;
        $this->value = $value;
        $this->label = $label;
        $this->required = $required;
        $this->accept = $accept;
    }

    public function render()
    {
        return view('components.image-upload');
    }
}