<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ActionMessage extends Component
{
    public $type;
    public $message;

    public function __construct($type = 'info', $message = '')
    {
        $this->type = $type;
        $this->message = $message;
    }

    public function render()
    {
        return view('components.action-message');
    }
}