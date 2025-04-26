<div class="bg-white rounded-lg shadow p-6">
    <h2 class="text-xl font-semibold mb-6">Notification Preferences</h2>

    <form wire:submit.prevent="savePreferences">
        <div class="space-y-6">
            @foreach(['content_published' => 'Content Published', 
                    'content_updated' => 'Content Updated',
                    'approval_required' => 'Approval Required',
                    'approval_completed' => 'Approval Completed'] as $type => $label)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium">{{ $label }}</h3>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model="preferences.{{ $type }}.enabled" class="sr-only peer">
                            <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sound</label>
                            <select wire:model="preferences.{{ $type }}.sound_id" 
                                    class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Default Sound</option>
                                @foreach($sounds as $sound)
                                    <option value="{{ $sound->id }}">{{ $sound->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Volume</label>
                            <div class="flex items-center space-x-2">
                                <input type="range" min="0" max="100" 
                                       wire:model="preferences.{{ $type }}.volume"
                                       class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer">
                                <span class="text-sm text-gray-600 w-12 text-center">
                                    {{ $preferences[$type]['volume'] }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                Save Preferences
            </button>
        </div>
    </form>

    @if (session()->has('message'))
        <div class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif
</div>