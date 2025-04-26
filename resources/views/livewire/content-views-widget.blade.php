@extends('livewire.analytics-widget')

@section('content')
    <div class="grid grid-cols-3 gap-4 mb-4">
        @foreach($data['metrics'] as $metric)
            <div class="bg-gray-50 p-3 rounded">
                <div class="text-sm text-gray-500">{{ $metric['label'] }}</div>
                <div class="text-2xl font-semibold">
                    @if($metric['key'] === 'content_type')
                        {{ count(explode(',', $metric['value'])) }} types
                    @else
                        {{ number_format($metric['value']) }}
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div x-data="{ chartType: '{{ $chartType }}' }" class="flex-1">
        <div class="flex justify-end mb-2">
            <select x-model="chartType" class="text-xs border rounded p-1">
                <option value="bar">Bar</option>
                <option value="pie">Pie</option>
            </select>
        </div>
        
        <div 
            x-init="
                new Chart(
                    $el,
                    {
                        type: chartType,
                        data: {
                            labels: {{ json_encode($data['chart_data']['labels']) }},
                            datasets: [{
                                data: {{ json_encode($data['chart_data']['values']) }},
                                backgroundColor: [
                                    '#3b82f6', '#10b981', '#6366f1', 
                                    '#f59e0b', '#ef4444', '#8b5cf6'
                                ],
                                borderColor: '#3b82f6',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'right' } }
                        }
                    }
                )
            "
            class="h-64"
        ></div>
    </div>
@endsection