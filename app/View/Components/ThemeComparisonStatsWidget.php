<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ThemeComparisonStatsWidget extends Component
{
    public string $title;
    public float $value;
    public string $type;
    public ?float $change;

    /**
     * Create a new component instance.
     *
     * @param string $title
     * @param float $value
     * @param string $type (score|complexity|performance)
     * @param float|null $change
     */
    public function __construct(string $title, float $value, string $type, ?float $change = null)
    {
        $this->title = $title;
        $this->value = $value;
        $this->type = $type;
        $this->change = $change;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.theme-comparison-stats-widget');
    }

    public function getColorClass(): string
    {
        if ($this->type === 'score') {
            if ($this->value >= 80) {
                return 'text-green-600';
            } elseif ($this->value >= 60) {
                return 'text-yellow-500';
            }
            return 'text-red-600';
        }

        if ($this->type === 'performance') {
            if ($this->value > 0) {
                return 'text-red-600';
            } elseif ($this->value < 0) {
                return 'text-green-600';
            }
            return 'text-gray-500';
        }

        if ($this->type === 'security') {
            return $this->value > 0 ? 'text-red-600' : 'text-green-600';
        }

        return 'text-gray-800';
    }

    public function getChangeColor(): string
    {
        if ($this->change === null) {
            return 'text-gray-500';
        }

        // For complexity, higher is worse (red) while lower is better (green)
        if ($this->type === 'complexity') {
            return $this->change <= 0 ? 'text-green-600' : 'text-red-600';
        }

        // For coverage and quality, higher is better (green) while lower is worse (red)
        return $this->change >= 0 ? 'text-green-600' : 'text-red-600';
    }

    public function getChangeIcon(): string
    {
        if ($this->change === null) {
            return '';
        }

        // For complexity, we want to show ↓ when improved (lower is better)
        if ($this->type === 'complexity') {
            return $this->change <= 0 ? '↓' : '↑';
        }

        // For other metrics, show ↑ when improved (higher is better)
        return $this->change >= 0 ? '↑' : '↓';
    }
}
