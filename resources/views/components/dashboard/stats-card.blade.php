<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between">
        <h3 class="text-gray-500 text-sm font-medium">{{ $title }}</h3>
        @if($icon)
            <div class="p-2 rounded-full bg-blue-100 text-blue-600">
                <x-icon name="{{ $icon }}" class="h-5 w-5" />
            </div>
        @endif
    </div>

    <div class="mt-4">
        <p class="text-2xl font-semibold">{{ $value }}</p>
        @if($trend)
            <div class="mt-2 flex items-center">
                @if($trendDirection === 'up')
                    <x-icon name="arrow-up" class="h-4 w-4 text-green-500" />
                @else
                    <x-icon name="arrow-down" class="h-4 w-4 text-red-500" />
                @endif
                <span class="ml-1 text-sm {{ $trendDirection === 'up' ? 'text-green-600' : 'text-red-600' }}">
                    {{ $trend }}
                </span>
            </div>
        @endif
    </div>
</div>