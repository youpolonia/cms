<?php

namespace App\View\Components;

use App\Models\ThemeVersion;
use Illuminate\View\Component;

class ThemeSizeComparison extends Component
{
    public $version1;
    public $version2;
    public $sizeMetrics;

    public function __construct(ThemeVersion $version1, ThemeVersion $version2)
    {
        $this->version1 = $version1;
        $this->version2 = $version2;
        $this->sizeMetrics = $this->calculateSizeMetrics();
    }

    protected function calculateSizeMetrics()
    {
        return [
            'version1' => [
                'total_size' => $this->version1->total_size_kb ?? 0,
                'assets_size' => $this->version1->assets_size_kb ?? 0,
                'templates_size' => $this->version1->templates_size_kb ?? 0,
                'scripts_size' => $this->version1->scripts_size_kb ?? 0,
                'styles_size' => $this->version1->styles_size_kb ?? 0,
            ],
            'version2' => [
                'total_size' => $this->version2->total_size_kb ?? 0,
                'assets_size' => $this->version2->assets_size_kb ?? 0,
                'templates_size' => $this->version2->templates_size_kb ?? 0,
                'scripts_size' => $this->version2->scripts_size_kb ?? 0,
                'styles_size' => $this->version2->styles_size_kb ?? 0,
            ],
            'difference' => [
                'total_size' => ($this->version2->total_size_kb ?? 0) - ($this->version1->total_size_kb ?? 0),
                'assets_size' => ($this->version2->assets_size_kb ?? 0) - ($this->version1->assets_size_kb ?? 0),
                'templates_size' => ($this->version2->templates_size_kb ?? 0) - ($this->version1->templates_size_kb ?? 0),
                'scripts_size' => ($this->version2->scripts_size_kb ?? 0) - ($this->version1->scripts_size_kb ?? 0),
                'styles_size' => ($this->version2->styles_size_kb ?? 0) - ($this->version1->styles_size_kb ?? 0),
            ]
        ];
    }

    public function render()
    {
        return view('components.theme-size-comparison', [
            'chartData' => [
                'labels' => ['Version '.$this->version1->version, 'Version '.$this->version2->version],
                'datasets' => [
                    [
                        'label' => 'Total Size (KB)',
                        'data' => [
                            $this->sizeMetrics['version1']['total_size'],
                            $this->sizeMetrics['version2']['total_size']
                        ],
                        'backgroundColor' => ['rgba(54, 162, 235, 0.5)', 'rgba(255, 99, 132, 0.5)'],
                        'borderColor' => ['rgba(54, 162, 235, 1)', 'rgba(255, 99, 132, 1)'],
                        'borderWidth' => 1
                    ]
                ]
            ]
        ]);
    }
}
