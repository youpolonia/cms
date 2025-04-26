<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Content Views</h3>
        <div class="flex space-x-2">
            <button 
                wire:click="$set('timeRange', '24h')"
                class="px-3 py-1 text-xs rounded-full {{ $timeRange === '24h' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}"
            >
                24h
            </button>
            <button 
                wire:click="$set('timeRange', '7d')"
                class="px-3 py-1 text-xs rounded-full {{ $timeRange === '7d' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}"
            >
                7d
            </button>
            <button 
                wire:click="$set('timeRange', '30d')"
                class="px-3 py-1 text-xs rounded-full {{ $timeRange === '30d' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}"
            >
                30d
            </button>
        </div>
    </div>

    <div class="h-64">
        <canvas 
            x-data="{
                chart: null,
                init() {
                    this.renderChart()
                    $wire.on('timeRangeChanged', () => {
                        this.renderChart()
                    })
                },
                renderChart() {
                    if (this.chart) {
                        this.chart.destroy()
                    }
                    
                    const ctx = this.$el.getContext('2d')
                    const labels = Object.keys($wire.viewsData)
                    const data = Object.values($wire.viewsData)
                    
                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Views',
                                data: data,
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
                    })
                }
            }"
            wire:ignore
        ></canvas>
    </div>

    <div class="mt-4 flex justify-between items-center">
        <div>
            <span class="text-sm text-gray-500">Total Views</span>
            <p class="text-2xl font-bold">
                {{ array_sum($this->viewsData) }}
            </p>
        </div>
        <div>
            <span class="text-sm text-gray-500">Avg. Daily</span>
            <p class="text-2xl font-bold">
                {{ round(array_sum($this->viewsData) / count($this->viewsData)) }}
            </p>
        </div>
    </div>
</div>