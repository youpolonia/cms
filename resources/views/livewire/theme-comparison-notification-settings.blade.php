<div>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-medium">Theme Comparison Notifications</h3>
                <p class="text-sm text-gray-500">
                    Get notified when theme versions have significant differences
                </p>
            </div>
            <x-toggle
                wire:model="enabled"
                id="themeComparisonEnabled"
            />
        </div>

        @if($enabled)
            <div class="space-y-2">
                <label for="threshold" class="block text-sm font-medium text-gray-700">
                    Notification Threshold (% of changes)
                </label>
                <div class="flex items-center space-x-4">
                    <input
                        type="range"
                        min="1"
                        max="100"
                        wire:model="threshold"
                        id="threshold"
                        class="w-full"
                    >
                    <span class="text-sm font-medium text-gray-700">
                        {{ $threshold }}%
                    </span>
                </div>
                <p class="text-xs text-gray-500">
                    Notifications will be sent when changes exceed this percentage
                </p>
            </div>
        @endif
    </div>

    @if($enabled && $threshold > 50)
        <div class="mt-4 p-4 bg-yellow-50 border-l-4 border-yellow-400">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        High threshold selected. You may receive frequent notifications.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
