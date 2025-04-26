@props(['theme', 'version'])

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <h2 class="text-lg font-medium text-gray-900">Version History</h2>
        <div class="flex space-x-2">
            <x-button.link href="{{ route('themes.versions.show', [$theme, $version]) }}">
                Back to Version
            </x-button.link>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="divide-y divide-gray-200">
            @forelse($version->history()->latest()->get() as $history)
                <div class="p-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900">
                                {{ ucfirst($history->event) }}
                            </p>
                            <p class="text-sm text-gray-500">
                                {{ $history->created_at->diffForHumans() }}
                                @if($history->user)
                                    by {{ $history->user->name }}
                                @endif
                            </p>
                        </div>
                        @if($history->event === 'rollback')
                            <div class="text-sm text-gray-500">
                                Rolled back from v{{ $history->details['from_version'] }} to v{{ $history->details['to_version'] }}
                            </div>
                        @endif
                    </div>

                    @if(!empty($history->details['changes']))
                        <div class="mt-2">
                            <div class="text-sm font-medium text-gray-900">Changes:</div>
                            <ul class="mt-1 text-sm text-gray-500 list-disc list-inside">
                                @foreach($history->details['changes'] as $change)
                                    <li>
                                        {{ $change['file'] }} ({{ $change['change'] }})
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @empty
                <div class="p-4 text-center text-gray-500">
                    No history available for this version
                </div>
            @endforelse
        </div>
    </div>
</div>
