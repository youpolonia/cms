@extends('layouts.app')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl font-semibold text-gray-900 mb-6">Analytics Dashboard</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Usage Stats Widget -->
            <div class="col-span-1">
                @livewire('usage-stats-widget', ['widgetType' => 'usage_stats'])
            </div>

            <!-- Content Views Widget -->
            <div class="col-span-1">
                @livewire('content-views-widget', ['widgetType' => 'content_views'])
            </div>

            <!-- AI Usage Widget -->
            <div class="col-span-1">
                @livewire('ai-usage-widget', ['widgetType' => 'ai_usage'])
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush
