@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Dashboard</h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @foreach($widgets as $widget)
                @if($widget['component'] === 'stats-card')
                    <x-dashboard.stats-card 
                        :title="$widget['data']['title']"
                        :value="$widget['data']['value']"
                        :icon="$widget['data']['icon'] ?? null"
                        :trend="$widget['data']['trend'] ?? null"
                        :trendDirection="$widget['data']['trendDirection'] ?? 'up'"
                    />
                @endif
            @endforeach
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($widgets as $widget)
                @if($widget['component'] === 'line-chart')
                    <x-dashboard.line-chart
                        :title="$widget['data']['title']"
                        :labels="$widget['data']['labels']"
                        :datasets="$widget['data']['datasets']"
                    />
                @endif
            @endforeach
        </div>
    </div>
</div>
@endsection
