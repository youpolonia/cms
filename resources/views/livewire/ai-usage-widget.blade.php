@extends('livewire.analytics-widget')

@section('content')
    @if($thresholdExceeded)
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">
                        AI usage threshold ({{ $threshold }}) exceeded!
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-3 gap-4 mb-4">
        @foreach($data['metrics'] as $metric)
            <div class="bg-gray-50 p-3 rounded">
                <div class="text-sm text-gray-500">{{ $metric['label'] }}</div>
                <div class="text-2xl font-semibold">
                    {{ $metric['key'] === 'tokens' ? number_format($metric['value']) : $metric['value'] }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex-1">
        <div 
            x-init="
                new Chart(
                    $el,
                    {
                        type: '{{ $chartType }}',
                        data: {
                            labels: {{ json_encode($data['chart_data']['labels']) }},
                            datasets: [{
                                data: {{ json_encode($data['chart_data']['values']) }},
                                backgroundColor: ['#3b82f6', '#10b981', '#6366f1'],
                                borderColor: '#fff',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { 
                                legend: { position: 'right' },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return context.label + ': ' + context.formattedValue;
                                        }
                                    }
                                }
                            }
                        }
                    }
                )
            "
            class="h-64"
        ></div>
    </div>
@endsection