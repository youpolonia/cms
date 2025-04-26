@props(['title' => null])

<div 
    wire:poll.{{ $refreshInterval }}s="loadData"
    wire:init="loadData"
    class="bg-white rounded-lg shadow p-4 h-full flex flex-col"
>
    @if($title)
        <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $title }}</h3>
    @endif

    @if($loading)
        <div class="flex-1 flex items-center justify-center">
            <svg class="animate-spin h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    @elseif(empty($data))
        <div class="flex-1 flex items-center justify-center text-gray-500">
            No data available
        </div>
    @else
        <div class="flex-1">
            {{ $slot }}
        </div>
    @endif

    <div class="mt-2 text-xs text-gray-500 text-right">
        Updated {{ now()->format('H:i:s') }}
    </div>
</div>