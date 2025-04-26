@props(['notification'])

@php
    $theme = $notification->data['theme_name'];
    $currentVersion = $notification->data['current_version'];
    $latestVersion = $notification->data['latest_version'];
@endphp

<div class="p-4 mb-4 bg-white rounded-lg shadow">
    <div class="flex items-start justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900">
                Theme Update Available: {{ $theme }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
                Current: v{{ $currentVersion }} → Latest: v{{ $latestVersion }}
            </p>
            
            @if($notification->data['changelog'] ?? false)
                <div class="mt-2 text-sm text-gray-700">
                    <h4 class="font-medium">Changelog:</h4>
                    <div class="mt-1 pl-4 border-l-2 border-gray-200">
                        {!! nl2br(e($notification->data['changelog'])) !!}
                    </div>
                </div>
            @endif
        </div>
        
        <div class="flex-shrink-0 ml-4">
            <a href="{{ route('themes.show', $notification->data['theme_id']) }}" 
               class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Update Now
            </a>
        </div>
    </div>
</div>
