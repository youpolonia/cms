<div>
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">
                @if($contentId)
                    Content Analytics
                @else
                    Content Performance Dashboard
                @endif
            </h2>
            <div class="flex space-x-2">
                @foreach($timeRanges as $value => $label)
                    <button 
                        wire:click="$set('timeRange', '{{ $value }}')"
                        class="px-4 py-2 text-sm font-medium rounded-md {{ $timeRange === $value ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}"
                    >
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <div wire:init="loadAnalytics">
            @if($loading)
                <div class="text-center py-8">
                    <div class="animate-pulse">Loading analytics data...</div>
                </div>
            @else
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h3 class="font-medium mb-4">Views Over Time</h3>
                            <div class="h-64">
                                <!-- Chart container -->
                                <canvas 
                                    wire:ignore
                                    x-data="{
                                        chart: null,
                                        init() {
                                            this.renderChart();
                                            $wire.on('analyticsLoaded', () => this.renderChart());
                                        },
                                        renderChart() {
                                            if (this.chart) this.chart.destroy();
                                            
                                            this.chart = new Chart(this.$el, {
                                                type: 'line',
                                                data: {
                                                    labels: @js($stats->pluck('date')),
                                                    datasets: [{
                                                        label: 'Views',
                                                        data: @js($stats->pluck('views')),
                                                        borderColor: '#3b82f6',
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
                                                            beginAtZero: true
                                                        }
                                                    }
                                                }
                                            });
                                        }
                                    }"
                                ></canvas>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="bg-gray-50 p-4 rounded-lg h-full">
                            <h3 class="font-medium mb-4">Top Performing Content</h3>
                            <div class="space-y-3">
                                @foreach($topContents as $content)
                                    <div class="border-b pb-3 last:border-b-0">
                                        <div class="flex justify-between items-start">
                                            <div>
                                                <h4 class="font-medium">{{ $content->title }}</h4>
                                                <p class="text-sm text-gray-500">{{ $content->views_count }} views</p>
                                            </div>
                                            <a 
                                                href="{{ route('contents.analytics', $content) }}"
                                                class="text-blue-600 text-sm hover:underline"
                                            >
                                                Details
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @endpush
</div>