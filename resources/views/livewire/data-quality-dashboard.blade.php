@php
    $timeRanges = [
        '1d' => 'Last 24 hours',
        '7d' => 'Last 7 days', 
        '30d' => 'Last 30 days',
        '90d' => 'Last 90 days'
    ];
@endphp

<div class="p-6 bg-white rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Export Data Quality Dashboard</h2>
        <div class="flex space-x-2">
            @foreach($timeRanges as $value => $label)
                <button 
                    wire:click="$emit('timeRangeChanged', '{{ $value }}')"
                    class="px-4 py-2 text-sm font-medium rounded-md {{ $timeRange === $value ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    @if($loading)
        <div class="flex justify-center items-center h-64">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
        </div>
    @else
        <!-- Quality Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-500">
                <h3 class="text-sm font-medium text-gray-500">Overall Quality</h3>
                <div class="mt-2 flex items-baseline">
                    <span class="text-3xl font-bold text-gray-900">{{ $metrics['overall'] }}%</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-500">
                <h3 class="text-sm font-medium text-gray-500">Completeness</h3>
                <div class="mt-2 flex items-baseline">
                    <span class="text-3xl font-bold text-gray-900">{{ $metrics['completeness'] }}%</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-purple-500">
                <h3 class="text-sm font-medium text-gray-500">Accuracy</h3>
                <div class="mt-2 flex items-baseline">
                    <span class="text-3xl font-bold text-gray-900">{{ $metrics['accuracy'] }}%</span>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-yellow-500">
                <h3 class="text-sm font-medium text-gray-500">Consistency</h3>
                <div class="mt-2 flex items-baseline">
                    <span class="text-3xl font-bold text-gray-900">{{ $metrics['consistency'] }}%</span>
                </div>
            </div>
        </div>

        <!-- Trend Chart -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Quality Trend</h3>
            <div x-data="{
                labels: {{ json_encode(array_column($trendData, 'date')) }},
                overall: {{ json_encode(array_column($trendData, 'overall')) }},
                completeness: {{ json_encode(array_column($trendData, 'completeness')) }},
                accuracy: {{ json_encode(array_column($trendData, 'accuracy')) }},
                init() {
                    const chart = new Chart(this.$refs.canvas, {
                        type: 'line',
                        data: {
                            labels: this.labels,
                            datasets: [
                                {
                                    label: 'Overall',
                                    data: this.overall,
                                    borderColor: '#3B82F6',
                                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                    tension: 0.1,
                                    fill: true
                                },
                                {
                                    label: 'Completeness',
                                    data: this.completeness,
                                    borderColor: '#10B981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    tension: 0.1,
                                    fill: true
                                },
                                {
                                    label: 'Accuracy',
                                    data: this.accuracy,
                                    borderColor: '#8B5CF6',
                                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                                    tension: 0.1,
                                    fill: true
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: { position: 'top' }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100
                                }
                            }
                        }
                    });
                }
            }" class="h-96">
                <canvas x-ref="canvas"></canvas>
            </div>
        </div>

        <!-- Anomalies Section -->
        @if(!empty($anomalies))
            <div class="bg-white p-6 rounded-lg shadow">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quality Anomalies Detected</h3>
                <div class="space-y-4">
                    @foreach($anomalies as $metric => $anomaly)
                        <div class="flex items-start p-4 bg-red-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h4 class="text-sm font-medium text-red-800">
                                    {{ ucfirst($metric) }} anomaly detected
                                </h4>
                                <div class="mt-1 text-sm text-red-700">
                                    <p>
                                        Current value: {{ $anomaly['current'] }}% ({{ $anomaly['deviation'] }}% deviation from average of {{ $anomaly['average'] }}%)
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endpush