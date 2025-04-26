<?php

namespace App\View\Components\Analytics;

use App\Models\ScheduledExportRun;
use Illuminate\View\Component;

class ExportStatusWidget extends Component
{
    public $recentExports;

    public function __construct($limit = 5)
    {
        $this->recentExports = ScheduledExportRun::with('export')
            ->latest()
            ->limit($limit)
            ->get()
            ->map(function ($run) {
                return [
                    'id' => $run->id,
                    'export' => [
                        'type' => $run->export->type,
                    ],
                    'status' => $run->status,
                    'created_at' => $run->created_at,
                ];
            });
    }

    public function render()
    {
        return view('components.analytics.export-status-widget', [
            'recentExports' => $this->recentExports
        ]);
    }
}