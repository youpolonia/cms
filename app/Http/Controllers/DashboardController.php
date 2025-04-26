<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Content;
use App\Models\ContentUserView;
use App\Models\AnalyticsExport;

class DashboardController extends Controller
{
    public function index()
    {
        $widgets = [
            [
                'component' => 'stats-card',
                'data' => [
                    'title' => 'Total Content',
                    'value' => Content::count(),
                    'icon' => 'document-text',
                    'trend' => '5%',
                    'trendDirection' => 'up'
                ]
            ],
            [
                'component' => 'stats-card',
                'data' => [
                    'title' => 'Total Views',
                    'value' => ContentUserView::sum('views'),
                    'icon' => 'eye',
                    'trend' => '12%',
                    'trendDirection' => 'up'
                ]
            ],
            [
                'component' => 'stats-card',
                'data' => [
                    'title' => 'Exports Today',
                    'value' => AnalyticsExport::today()->count(),
                    'icon' => 'download',
                    'trend' => '3%',
                    'trendDirection' => 'down'
                ]
            ],
            [
                'component' => 'line-chart',
                'data' => [
                    'title' => 'Content Views (7 Days)',
                    'labels' => now()->subDays(6)->daysUntil(now())->map(fn ($date) => $date->format('M j'))->toArray(),
                    'datasets' => [
                        [
                            'label' => 'Views',
                            'data' => $this->getWeeklyViewsData(),
                            'borderColor' => 'rgb(59, 130, 246)',
                            'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                            'tension' => 0.3
                        ]
                    ]
                ]
            ]
        ];

        return view('dashboard', compact('widgets'));
    }

    protected function getWeeklyViewsData(): array
    {
        return collect(range(6, 0))->map(function ($daysAgo) {
            return ContentUserView::whereDate('created_at', today()->subDays($daysAgo))
                ->sum('views');
        })->toArray();
    }
}