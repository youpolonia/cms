@extends('livewire.analytics-widget')

@section('content')
    <div class="grid grid-cols-3 gap-4 mb-4">
        @foreach($data['metrics'] as $metric)
            <div class="bg-gray-50 p-3 rounded">
                <div class="text-sm text-gray-500">{{ $metric['label'] }}</div>
                <div class="text-2xl font-semibold">{{ $metric['value'] }}</div>
            </div>
        @endforeach
    </div>

    <div x-data="{ chartType: '{{ $chartType }}' }" class="flex-1">
        <div class="flex justify-end mb-2">
            <select x-model="chartType" class="text-xs border rounded p-1">
                <option value="line">Line</option>
                <option value="bar">Bar</option>
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
                                backgroundColor: ['#3b82f6', '#10b981', '#6366f1'],
                                borderColor: '#3b82f6',
                                tension: 0.1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } }
                        }
                    }
                )
            "
            class="h-64"
        ></div>
    </div>
@endsection