@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Theme Version Comparison Analytics</h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Comparison Statistics</h2>
            <div class="flex space-x-2">
                <a href="{{ route('theme.comparison.export', 'csv') }}" 
                   class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                    Export CSV
                </a>
                <a href="{{ route('theme.comparison.export', 'json') }}" 
                   class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
                    Export JSON
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">From Version</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">To Version</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File Count Diff</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size Diff</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quality Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Compared At</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($stats as $stat)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $stat->fromVersion->version_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $stat->toVersion->version_number }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $stat->file_count_diff }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ formatBytes($stat->size_diff) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $stat->quality_score_diff }}%</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $stat->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $stats->links() }}
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Comparison Charts</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <canvas id="fileCountChart" height="300"></canvas>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <canvas id="sizeDiffChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // File Count Chart
        new Chart(document.getElementById('fileCountChart'), {
            type: 'bar',
            data: {
                labels: @json($stats->pluck('id')),
                datasets: [{
                    label: 'File Count Difference',
                    data: @json($stats->pluck('file_count_diff')),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Files: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });

        // Size Difference Chart
        new Chart(document.getElementById('sizeDiffChart'), {
            type: 'line',
            data: {
                labels: @json($stats->pluck('id')),
                datasets: [{
                    label: 'Size Difference (bytes)',
                    data: @json($stats->pluck('size_diff')),
                    backgroundColor: 'rgba(75, 192, 192, 0.5)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1,
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `Size: ${context.raw} bytes`;
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
