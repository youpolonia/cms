<div class="bg-white rounded-lg shadow-md p-4">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Version Comparison</h3>
        <button wire:click="toggleViewMode" class="btn btn-sm btn-outline">
            {{ $showSideBySide ? 'Inline View' : 'Side-by-Side View' }}
        </button>
    </div>

    <div class="mb-4">
        <div class="grid grid-cols-2 gap-4 mb-2">
            <div class="bg-gray-50 p-2 rounded">
                <p class="font-medium">From: Version {{ $fromVersion->version_number }}</p>
                <p class="text-sm text-gray-500">{{ $fromVersion->created_at->format('M d, Y H:i') }}</p>
            </div>
            <div class="bg-gray-50 p-2 rounded">
                <p class="font-medium">To: Version {{ $toVersion->version_number }}</p>
                <p class="text-sm text-gray-500">{{ $toVersion->created_at->format('M d, Y H:i') }}</p>
            </div>
        </div>
        <p class="text-sm text-gray-600">{{ $summary }}</p>
    </div>

    @if($showSideBySide)
        <div class="border rounded overflow-hidden">
            <div class="grid grid-cols-12 bg-gray-100 p-2 font-medium">
                <div class="col-span-1">Line</div>
                <div class="col-span-5">From Version</div>
                <div class="col-span-5">To Version</div>
                <div class="col-span-1">Type</div>
            </div>
            @foreach($changes as $index => $change)
                @php
                    $bgColor = $index === $activeChange ? 'bg-blue-50' : (
                        str_starts_with($change, '+') ? 'bg-green-50' : (
                            str_starts_with($change, '-') ? 'bg-red-50' : 'bg-white'
                        )
                    );
                @endphp
                <div class="grid grid-cols-12 border-t {{ $bgColor }} p-2 hover:bg-gray-50 cursor-pointer"
                     wire:click="$emit('changeSelected', {{ $index }})">
                    <div class="col-span-1">{{ $index + 1 }}</div>
                    <div class="col-span-5 font-mono text-sm break-all">
                        @if(str_starts_with($change, '-') || str_starts_with($change, ' '))
                            {{ substr($change, 1) }}
                        @endif
                    </div>
                    <div class="col-span-5 font-mono text-sm break-all">
                        @if(str_starts_with($change, '+') || str_starts_with($change, ' '))
                            {{ substr($change, 1) }}
                        @endif
                    </div>
                    <div class="col-span-1 text-center">
                        @if(str_starts_with($change, '+'))
                            <span class="text-green-600">+</span>
                        @elseif(str_starts_with($change, '-'))
                            <span class="text-red-600">-</span>
                        @else
                            <span class="text-gray-400">=</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="border rounded overflow-hidden">
            <div class="bg-gray-100 p-2 font-medium">Inline Changes</div>
            @foreach($changes as $index => $change)
                @php
                    $bgColor = $index === $activeChange ? 'bg-blue-50' : (
                        str_starts_with($change, '+') ? 'bg-green-50' : (
                            str_starts_with($change, '-') ? 'bg-red-50' : 'bg-white'
                        )
                    );
                @endphp
                <div class="border-t {{ $bgColor }} p-2 hover:bg-gray-50 cursor-pointer"
                     wire:click="$emit('changeSelected', {{ $index }})">
                    <div class="font-mono text-sm break-all">
                        @if(str_starts_with($change, '+'))
                            <span class="text-green-600">+</span> {{ substr($change, 1) }}
                        @elseif(str_starts_with($change, '-'))
                            <span class="text-red-600">-</span> {{ substr($change, 1) }}
                        @else
                            <span class="text-gray-400"> </span> {{ substr($change, 1) }}
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>