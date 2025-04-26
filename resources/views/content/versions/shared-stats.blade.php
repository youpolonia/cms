@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-4">Shared Comparison Statistics</h1>
        
        <div class="mb-6">
            <p class="text-gray-600 mb-2">This comparison was shared with you by {{ $sharedBy->name }}.</p>
            <p class="text-sm text-gray-500">Shared on {{ $sharedAt->format('M d, Y H:i') }}</p>
            <p class="text-sm text-gray-500">Viewed {{ $accessLog->view_count }} times</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-blue-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-2">Content Changes</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Total Changes</p>
                        <p class="text-2xl font-bold">{{ $stats->change_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Additions</p>
                        <p class="text-2xl font-bold text-green-600">{{ $stats->additions }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Deletions</p>
                        <p class="text-2xl font-bold text-red-600">{{ $stats->deletions }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-2">Engagement</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Total Views</p>
                        <p class="text-2xl font-bold">{{ $accessLog->view_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Unique Visitors</p>
                        <p class="text-2xl font-bold">{{ $accessLog->unique_visitors }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Last Viewed</p>
                        <p class="text-lg">{{ $accessLog->last_viewed_at->diffForHumans() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-green-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-2">Metadata</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500">Original Version</p>
                        <p class="text-lg">{{ $originalVersion->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Modified Version</p>
                        <p class="text-lg">{{ $modifiedVersion->created_at->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Time Between Versions</p>
                        <p class="text-lg">{{ $originalVersion->created_at->diffForHumans($modifiedVersion->created_at, true) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white border rounded-lg p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Analytics</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h3 class="text-lg font-medium mb-4">Views Over Time</h3>
                    <canvas id="viewsChart" height="300"></canvas>
                </div>
                <div>
                    <h3 class="text-lg font-medium mb-4">Device Breakdown</h3>
                    <canvas id="devicesChart" height="300"></canvas>
                </div>
                <div class="md:col-span-2">
                    <h3 class="text-lg font-medium mb-4">Top Locations</h3>
                    <canvas id="locationsChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white border rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">View History</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Viewed At</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Agent</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($viewHistory as $view)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $view->created_at->format('M d, Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $view->ip_address }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 truncate max-w-xs">{{ $view->user_agent }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch analytics data
    fetch('/api/content/analytics/shared-stats/{{ $comparison->id }}')
        .then(response => response.json())
        .then(data => {
            // Views over time chart
            new Chart(
                document.getElementById('viewsChart'),
                {
                    type: 'line',
                    data: {
                        labels: data.views_over_time.labels,
                        datasets: [{
                            label: 'Views',
                            data: data.views_over_time.data,
                            borderColor: 'rgb(59, 130, 246)',
                            tension: 0.1,
                            fill: true
                        }]
                    }
                }
            );

            // Device breakdown chart
            new Chart(
                document.getElementById('devicesChart'),
                {
                    type: 'doughnut',
                    data: {
                        labels: data.device_breakdown.labels,
                        datasets: [{
                            data: data.device_breakdown.data,
                            backgroundColor: [
                                'rgb(59, 130, 246)',
                                'rgb(168, 85, 247)',
                                'rgb(16, 185, 129)'
                            ]
                        }]
                    }
                }
            );

            // Locations chart
            new Chart(
                document.getElementById('locationsChart'),
                {
                    type: 'bar',
                    data: {
                        labels: data.location_breakdown.labels,
                        datasets: [{
                            label: 'Views',
                            data: data.location_breakdown.data,
                            backgroundColor: 'rgb(59, 130, 246)'
                        }]
                    }
                }
            );
        });
});
</script>
@endpush
