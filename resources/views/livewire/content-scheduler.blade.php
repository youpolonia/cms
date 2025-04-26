<div class="bg-white shadow rounded-lg p-6">
    <h3 class="text-lg font-medium mb-4">Content Scheduling</h3>

    <form wire:submit.prevent="saveSchedule">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block font-medium text-sm text-gray-700 mb-1">Publish Date/Time</label>
                <input type="datetime-local" wire:model="publishAt" 
                       class="w-full rounded-md border-gray-300 shadow-sm">
                @error('publishAt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block font-medium text-sm text-gray-700 mb-1">Expiration Date/Time</label>
                <input type="datetime-local" wire:model="expireAt" 
                       class="w-full rounded-md border-gray-300 shadow-sm">
                @error('expireAt') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="mt-4 flex items-center">
            <input type="checkbox" wire:model="isScheduled" id="isScheduled" 
                   class="rounded border-gray-300 text-blue-600 shadow-sm">
            <label for="isScheduled" class="ml-2 text-sm text-gray-700">Enable Scheduling</label>
        </div>

        <div class="mt-6">
            <button type="submit" 
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                Save Schedule
            </button>
        </div>
    </form>

    @if($content->publish_at)
        <div class="mt-6 pt-6 border-t">
            <h4 class="font-medium mb-2">Current Schedule</h4>
            <p>Publish: {{ $content->publish_at->format('M j, Y g:i A') }}</p>
            @if($content->expire_at)
                <p>Expire: {{ $content->expire_at->format('M j, Y g:i A') }}</p>
            @endif
        </div>
    @endif
</div>