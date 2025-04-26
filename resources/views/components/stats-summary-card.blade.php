@props([
    'title',
    'value',
    'type' => 'count',
    'trend' => null, // 'up' or 'down'
    'trendValue' => null,
    'color' => 'gray',
    'icon' => null
])

@php
    $colors = [
        'gray' => 'bg-gray-100 text-gray-800',
        'red' => 'bg-red-100 text-red-800',
        'yellow' => 'bg-yellow-100 text-yellow-800',
        'green' => 'bg-green-100 text-green-800',
        'blue' => 'bg-blue-100 text-blue-800',
        'indigo' => 'bg-indigo-100 text-indigo-800',
        'purple' => 'bg-purple-100 text-purple-800',
        'pink' => 'bg-pink-100 text-pink-800',
    ];

    $trendColors = [
        'up' => 'text-green-600',
        'down' => 'text-red-600'
    ];

    $formattedValue = $type === 'percentage' 
        ? $value . '%' 
        : ($type === 'duration' 
            ? $value . ' days' 
            : number_format($value));
@endphp

<div class="bg-white rounded-lg shadow p-4">
    <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-500">{{ $title }}</h3>
        @if($icon)
        <div class="p-2 rounded-full {{ $colors[$color] }}">
            {!! $icon !!}
        </div>
        @endif
    </div>
    
    <div class="mt-2">
        <p class="text-2xl font-semibold text-gray-900">{{ $formattedValue }}</p>
        
        @if($trend && $trendValue)
        <div class="flex items-center mt-1">
            @if($trend === 'up')
            <svg class="w-4 h-4 {{ $trendColors[$trend] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
            </svg>
            @else
            <svg class="w-4 h-4 {{ $trendColors[$trend] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
            @endif
            <span class="ml-1 text-sm {{ $trendColors[$trend] }}">
                {{ $trendValue }}
            </span>
        </div>
        @endif
    </div>
</div>
