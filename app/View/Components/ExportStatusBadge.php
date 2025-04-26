<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ExportStatusBadge extends Component
{
    public string $status;
    
    public function __construct(string $status)
    {
        $this->status = $status;
    }

    public function render()
    {
        $classes = [
            'completed' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'processing' => 'bg-blue-100 text-blue-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'expired' => 'bg-gray-100 text-gray-800'
        ];

        return view('components.export-status-badge', [
            'status' => $this->status,
            'class' => $classes[$this->status] ?? 'bg-gray-100 text-gray-800'
        ]);
    }
}