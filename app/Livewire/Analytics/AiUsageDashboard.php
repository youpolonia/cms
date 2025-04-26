<?php

namespace App\Livewire\Analytics;

use Livewire\Component;
use App\Models\User;
use App\Services\OpenAIService;
use Illuminate\Support\Facades\RateLimiter;

class AiUsageDashboard extends Component
{
    public $timeframe = 'week';
    public $metrics = [];
    public $topUsers = [];
    public $toolUsage = [];
    public $userStats = [];
    public $rateLimits = [];

    protected $listeners = ['timeframeUpdated' => 'updateTimeframe'];

    public function mount()
    {
        $this->loadData();
    }

    public function updateTimeframe($timeframe)
    {
        $this->timeframe = $timeframe;
        $this->loadData();
    }

    public function loadData()
    {
        // Global metrics
        $this->metrics = [
            'total_usage' => User::sum('ai_usage_count'),
            'avg_per_user' => User::avg('ai_usage_count'),
            'active_users' => User::where('ai_usage_count', '>', 0)->count()
        ];

        // Top users
        $this->topUsers = User::orderByDesc('ai_usage_count')
            ->limit(5)
            ->get(['name', 'ai_usage_count', 'last_ai_used_at'])
            ->toArray();

        // Current user stats
        $this->userStats = [
            'total_usage' => auth()->user()->ai_usage_count,
            'last_used' => auth()->user()->last_ai_used_at,
            'usage_by_type' => [
                'generate' => auth()->user()->ai_usage_generate_count ?? 0,
                'moderate' => auth()->user()->ai_usage_moderate_count ?? 0,
                'suggest' => auth()->user()->ai_usage_suggest_count ?? 0,
                'diff' => auth()->user()->ai_usage_diff_count ?? 0
            ]
        ];

        // Rate limit status
        $this->rateLimits = [
            'generate' => $this->getRateLimitStatus('generate'),
            'moderate' => $this->getRateLimitStatus('moderate'),
            'suggest' => $this->getRateLimitStatus('suggest'),
            'diff' => $this->getRateLimitStatus('diff')
        ];
    }

    protected function getRateLimitStatus(string $type): array
    {
        $key = 'ai_usage:'.auth()->id().':'.$type;
        return [
            'remaining' => RateLimiter::remaining($key, 5),
            'available_in' => RateLimiter::tooManyAttempts($key, 5)
                ? RateLimiter::availableIn($key)
                : 0
        ];
    }

    public function render()
    {
        return view('livewire.analytics.ai-usage-dashboard');
    }
}
