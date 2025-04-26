@component('mail::notification')
    <div class="flex items-start">
        <div class="flex-shrink-0 pt-0.5">
            <x-icon name="rollback" class="h-10 w-10 text-gray-400" />
        </div>
        <div class="ml-4 flex-1">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-900">
                    Theme Rollback: {{ $theme->name }}
                </p>
                <p class="text-sm text-gray-500">
                    {{ $rollback->created_at->diffForHumans() }}
                </p>
            </div>
            <p class="text-sm text-gray-500">
                Rolled back from version {{ $rollback->version->id }} to {{ $rollback->rollbackToVersion->id }}
            </p>
            
            @if($rollback->file_changes)
            <div class="mt-2 text-sm">
                <span class="font-medium text-gray-900">File Changes:</span>
                <ul class="list-disc pl-5 mt-1 space-y-1">
                    @foreach(json_decode($rollback->file_changes, true) as $change)
                    <li class="text-sm text-gray-500">
                        {{ $change['path'] }} ({{ $change['type'] }})
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="mt-4 flex">
                <a href="{{ route('themes.versions.rollback.details', [$theme, $rollback]) }}" 
                   class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    View Details
                </a>
            </div>
        </div>
    </div>
@endcomponent
