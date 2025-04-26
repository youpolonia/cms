@extends('layouts.app')

@section('content')
    <div class="max-w-6xl mx-auto py-8">
        <div class="flex justify-between items-center mb-8">
            <div class="flex items-center space-x-4">
                <h1 class="text-3xl font-bold">{{ $category->name }} Dashboard</h1>
                <button onclick="window.location.reload()" 
                        class="p-1 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-full"
                        title="Refresh Data">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
            <div class="text-sm text-gray-500">
                Last refreshed: {{ now()->format('H:i:s') }}
            </div>
            <div class="flex space-x-2">
                <div class="relative">
                    <form method="GET" action="{{ route('categories.dashboard', $category) }}">
                        <select name="status" onchange="this.form.submit()" 
                                class="appearance-none bg-white border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Content</option>
                            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Drafts</option>
                            <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                    </form>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('categories.edit', $category) }}"
                       class="px-3 py-1 bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Category
                    </a>
                    <a href="{{ route('categories.edit', $category) }}#content-management"
                       class="px-3 py-1 bg-purple-100 text-purple-800 rounded-md hover:bg-purple-200 text-sm flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                        Manage Contents
                    </a>
                    <div class="relative">
                        <button class="px-3 py-1 bg-gray-100 text-gray-800 rounded-md hover:bg-gray-200 text-sm flex items-center">
                            More
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="absolute right-0 mt-1 w-48 bg-white rounded-md shadow-lg py-1 z-10 hidden group-hover:block">
                            <a href="{{ route('categories.show', $category) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Content</a>
                    <a href="{{ route('categories.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">All Categories</a>
                    <div class="border-t border-gray-100"></div>
                    <a href="{{ route('content.create', ['category_id' => $category->id]) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Add New Content</a>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Quick Stats</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="border-l-4 border-blue-500 pl-4">
                    <div class="text-sm text-gray-500">Total Content</div>
                    <div class="text-2xl font-bold">{{ $stats['content_count'] }}</div>
                </div>
                <div class="border-l-4 border-green-500 pl-4">
                    <div class="text-sm text-gray-500">Active Subcategories</div>
                    <div class="text-2xl font-bold">{{ $stats['subcategories'] }}</div>
                </div>
                <div class="border-l-4 border-purple-500 pl-4">
                    <div class="text-sm text-gray-500">Avg. Engagement</div>
                    <div class="text-2xl font-bold">{{ round($stats['visitor_stats']['avg_time_spent']/60, 1) }} min</div>
                </div>
                <div class="border-l-4 border-yellow-500 pl-4">
                    <div class="text-sm text-gray-500">Bounce Rate</div>
                    <div class="text-2xl font-bold">{{ round($stats['visitor_stats']['bounce_rate']) }}%</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6 relative group">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Content Count</h3>
                    <span class="text-xs text-gray-500">
                        Updated: {{ now()->format('H:i') }}
                    </span>
                </div>
                <p class="text-4xl font-bold">{{ $stats['content_count'] }}</p>
                <div class="absolute hidden group-hover:block z-20 w-64 bg-white shadow-xl rounded-lg p-4 border border-gray-200 mt-2">
                    <h4 class="font-medium mb-2">Recent Content</h4>
                    <ul class="space-y-2 max-h-60 overflow-y-auto">
                        @foreach($stats['recent_content'] as $content)
                            <li class="text-sm">
                                <div class="flex justify-between items-start">
                                    <a href="{{ route('content.show', $content) }}" 
                                       class="text-blue-600 hover:underline flex-1 truncate">
                                        {{ $content->title }}
                                    </a>
                                    <span class="text-xs px-2 py-1 rounded-full 
                                        @if($content->status === 'published') bg-green-100 text-green-800
                                        @elseif($content->status === 'draft') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($content->status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $content->created_at->diffForHumans() }}
                                </div>
                                @if($content->categories->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-1">
                                    @foreach($content->categories->take(3) as $cat)
                                        <span class="text-xs px-1 py-0.5 rounded-full bg-blue-100 text-blue-800">
                                            {{ $cat->name }}
                                        </span>
                                    @endforeach
                                    @if($content->categories->count() > 3)
                                        <span class="text-xs text-gray-500">+{{ $content->categories->count() - 3 }} more</span>
                                    @endif
                                </div>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Subcategories</h3>
                <p class="text-4xl font-bold">{{ $stats['subcategories'] }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Total Views</h3>
                <p class="text-4xl font-bold">{{ number_format($stats['visitor_stats']['total_views']) }}</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Avg. Time</h3>
                <p class="text-2xl font-bold">{{ round($stats['visitor_stats']['avg_time_spent']/60, 1) }} min</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-2">Performance Score</h3>
                <div class="flex items-center">
                    <div class="relative w-24 h-24">
                        <svg class="w-full h-full" viewBox="0 0 36 36">
                            <path
                                d="M18 2.0845
                                a 15.9155 15.9155 0 0 1 0 31.831
                                a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none"
                                stroke="#eee"
                                stroke-width="3"
                            />
                            <path
                                d="M18 2.0845
                                a 15.9155 15.9155 0 0 1 0 31.831
                                a 15.9155 15.9155 0 0 1 0 -31.831"
                                fill="none"
                                stroke="#4CAF50"
                                stroke-width="3"
                                stroke-dasharray="{{ $stats['performance_score'] }}, 100"
                            />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-2xl font-bold">{{ $stats['performance_score'] }}</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <div class="text-sm text-gray-500">Content: {{ $stats['content_count'] }}</div>
                        <div class="text-sm text-gray-500">Engagement: {{ round($stats['visitor_stats']['avg_time_spent']/60, 1) }} min</div>
                        <div class="text-sm text-gray-500">Bounce Rate: {{ round($stats['visitor_stats']['bounce_rate']) }}%</div>
                    </div>
                </div>
            </div>
                <div class="flex items-center">
                    <p class="text-2xl font-bold mr-2">{{ round($stats['visitor_stats']['bounce_rate']) }}%</p>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="h-2.5 rounded-full 
                            @if($stats['visitor_stats']['bounce_rate'] < 40) bg-green-500
                            @elseif($stats['visitor_stats']['bounce_rate'] < 70) bg-yellow-500
                            @else bg-red-500 @endif" 
                            style="width: {{ $stats['visitor_stats']['bounce_rate'] }}%">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Popular Keywords</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($stats['popular_keywords'] as $keyword)
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                        {{ $keyword }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Content Status Distribution</h2>
                <div class="h-64">
                    <canvas id="statusDistributionChart"></canvas>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-xl font-bold mb-4">Content Creation Trend</h2>
            <div class="h-64">
                <canvas id="contentTrendChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-xl font-bold mb-4">Related Categories</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                @foreach($category->siblings()->withCount('contents')->get() as $sibling)
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <h3 class="font-medium mb-1">{{ $sibling->name }}</h3>
                    <div class="text-sm text-gray-500 mb-2">{{ $sibling->contents_count }} contents</div>
                    <a href="{{ route('categories.dashboard', $sibling) }}" 
                       class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                        View Dashboard
                    </a>
                </div>
                @endforeach
            </div>

            <h2 class="text-xl font-bold mb-4">AI Content Suggestions</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach($aiSuggestions as $suggestion)
                <div class="border rounded-lg p-4 hover:bg-gray-50">
                    <h3 class="font-medium mb-2">{{ $suggestion }}</h3>
                    <div class="flex justify-between items-center mt-3">
                        <button onclick="window.location='{{ route('content.create', ['category_id' => $category->id, 'title' => $suggestion]) }}'" 
                                class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded hover:bg-blue-200">
                            Create
                        </button>
                        <button onclick="generateContent('{{ $suggestion }}')" 
                                class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded hover:bg-green-200">
                            Generate
                        </button>
                    </div>
                </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end space-x-2">
            <a href="{{ route('categories.export', $category) }}" 
               class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Report
            </a>
            <a href="{{ route('categories.show', $category) }}" 
               class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                View Category
            </a>
        </div>

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Status Distribution Pie Chart
                const statusCtx = document.getElementById('statusDistributionChart');
                const statusData = {
                    labels: Object.keys(@json($stats['status_distribution'])),
                    datasets: [{
                        data: Object.values(@json($stats['status_distribution'])),
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 206, 86, 0.7)',
                            'rgba(75, 192, 192, 0.7)',
                            'rgba(153, 102, 255, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                };

                new Chart(statusCtx, {
                    type: 'pie',
                    data: statusData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = Math.round((value / total) * 100);
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });

                // Content Trend Chart
                const trendCtx = document.getElementById('contentTrendChart');
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const ctx = document.getElementById('contentTrendChart');
                const trendData = @json($stats['content_trends']);
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: Object.keys(trendData),
                        datasets: [{
                            label: 'Content Created',
                            data: Object.values(trendData),
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.1,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            });
        </script>
        @endpush
    </div>
@endsection
