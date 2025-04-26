<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ContentGenerator extends Component
{
    public $templates = [
        'content_suggestion' => 'Content Suggestions',
        'seo_optimization' => 'SEO Optimization',
        'content_enhancement' => 'Content Enhancement',
        'content_summary' => 'Content Summary'
    ];

    public $usageStats;
    public $remainingCredits;

    public function __construct()
    {
        try {
            $response = Http::withToken(auth()->user()->currentAccessToken()->token)
                ->get('/ai/usage');
                
            $this->usageStats = $response->json();
            $this->remainingCredits = $this->usageStats['remaining'] ?? 0;
        } catch (\Exception $e) {
            $this->usageStats = [];
            $this->remainingCredits = 0;
        }
    }

    public function render()
    {
        return view('components.content-generator');
    }
}