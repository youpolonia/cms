<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;

class ApprovalAnalyticsDashboard extends Component
{
    public $stats = [];
    public $workflow = [];
    public $pending = [];
    public $loading = true;

    protected $listeners = ['refreshAnalytics' => 'loadData'];

    public function mount()
    {
        $this->loadData();
    }

    public function loadData()
    {
        $this->loading = true;
        
        $response = Http::get('/api/analytics/approvals');
        if ($response->successful()) {
            $data = $response->json();
            $this->stats = $data['stats'];
            $this->workflow = $data['workflow']; 
            $this->pending = $data['pending']['data'] ?? [];
        }

        $this->loading = false;
    }

    public function refresh()
    {
        Http::post('/api/analytics/approvals/refresh')
            ->then(function() {
                $this->emit('refreshAnalytics');
            });
    }

    public function render()
    {
        return view('livewire.approval-analytics-dashboard');
    }
}