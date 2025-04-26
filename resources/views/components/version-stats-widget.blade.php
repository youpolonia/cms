<div class="bg-white rounded-lg shadow-md p-6 space-y-6">
    <div>
        <h3 class="text-lg font-semibold mb-4">Version Activity (Last 30 Days)</h3>
        <div x-data="versionActivityChart()" x-init="initChart()" class="h-64">
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <h3 class="text-lg font-semibold mb-4">Top Contributors</h3>
            <ul class="space-y-3">
                @foreach($userContributions as $contributor)
                <li class="flex justify-between items-center">
                    <span class="font-medium">{{ $contributor->user->name }}</span>
                    <span class="text-gray-500">{{ $contributor->count }} versions</span>
                </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 class="text-lg font-semibold mb-4">Content Type Distribution</h3>
            <div x-data="contentTypeChart()" x-init="initChart()" class="h-64">
                <canvas id="typeChart"></canvas>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function versionActivityChart() {
    return {
        chart: null,
        initChart() {
            const ctx = document.getElementById('activityChart').getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($activityData->pluck('date')) !!},
                    datasets: [{
                        label: 'Versions',
                        data: {!! json_encode($activityData->pluck('count')) !!},
                        borderColor: '#3B82F6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
}

function contentTypeChart() {
    return {
        chart: null,
        initChart() {
            const ctx = document.getElementById('typeChart').getContext('2d');
            this.chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: {!! json_encode($contentTypeDistribution->pluck('version.contentType.name')) !!},
                    datasets: [{
                        data: {!! json_encode($contentTypeDistribution->pluck('count')) !!},
                        backgroundColor: [
                            '#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
}
</script>
@endpush