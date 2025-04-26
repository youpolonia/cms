<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Size Comparison (KB)</h3>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="w-full h-96">
            <canvas id="sizeComparisonChart"></canvas>
        </div>
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metric</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version {{ $version1->version }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version {{ $version2->version }}</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difference</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Total Size</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version1']['total_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version2']['total_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $sizeMetrics['difference']['total_size'] >= 0 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $sizeMetrics['difference']['total_size'] >= 0 ? '+' : '' }}{{ number_format($sizeMetrics['difference']['total_size'], 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Assets</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version1']['assets_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version2']['assets_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $sizeMetrics['difference']['assets_size'] >= 0 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $sizeMetrics['difference']['assets_size'] >= 0 ? '+' : '' }}{{ number_format($sizeMetrics['difference']['assets_size'], 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Templates</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version1']['templates_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version2']['templates_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $sizeMetrics['difference']['templates_size'] >= 0 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $sizeMetrics['difference']['templates_size'] >= 0 ? '+' : '' }}{{ number_format($sizeMetrics['difference']['templates_size'], 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Scripts</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version1']['scripts_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version2']['scripts_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $sizeMetrics['difference']['scripts_size'] >= 0 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $sizeMetrics['difference']['scripts_size'] >= 0 ? '+' : '' }}{{ number_format($sizeMetrics['difference']['scripts_size'], 2) }}
                    </td>
                </tr>
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Styles</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version1']['styles_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ number_format($sizeMetrics['version2']['styles_size'], 2) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm {{ $sizeMetrics['difference']['styles_size'] >= 0 ? 'text-red-500' : 'text-green-500' }}">
                        {{ $sizeMetrics['difference']['styles_size'] >= 0 ? '+' : '' }}{{ number_format($sizeMetrics['difference']['styles_size'], 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('sizeComparisonChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: @json($chartData),
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Size (KB)'
                            }
                        }
                    }
                }
            });
        });
    </script>
@endpush
