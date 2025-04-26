<div>
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Add New Sound</h2>
        <form wire:submit.prevent="saveSound">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <input type="text" wire:model="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Category</label>
                    <select wire:model="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="notification">Notification</option>
                        <option value="alert">Alert</option>
                        <option value="feedback">Feedback</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea wire:model="description" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Sound File</label>
                    <input type="file" wire:model="newSound" class="mt-1 block w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4
                        file:rounded-md file:border-0
                        file:text-sm file:font-semibold
                        file:bg-blue-50 file:text-blue-700
                        hover:file:bg-blue-100">
                    @error('newSound') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <input wire:model="isDefault" id="isDefault" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="isDefault" class="ml-2 block text-sm text-gray-700">Set as default</label>
                    </div>
                    <div class="flex items-center">
                        <input wire:model="isActive" id="isActive" type="checkbox" class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" checked>
                        <label for="isActive" class="ml-2 block text-sm text-gray-700">Active</label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-blue-900 focus:ring focus:ring-blue-300 disabled:opacity-25 transition">
                    Upload Sound
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold">Manage Sounds</h2>
        </div>
        <div class="divide-y divide-gray-200">
            @foreach($sounds as $sound)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div>
                            <h3 class="text-lg font-medium">{{ $sound->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $sound->description }}</p>
                            <div class="flex items-center space-x-2 mt-1">
                                <span class="px-2 py-1 text-xs rounded-full {{ $sound->category === 'notification' ? 'bg-blue-100 text-blue-800' : ($sound->category === 'alert' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800') }}">
                                    {{ ucfirst($sound->category) }}
                                </span>
                                @if($sound->is_default)
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                        Default
                                    </span>
                                @endif
                                <span class="px-2 py-1 text-xs rounded-full {{ $sound->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $sound->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button wire:click="toggleActive({{ $sound->id }})" class="px-3 py-1 text-sm rounded {{ $sound->is_active ? 'bg-gray-200 text-gray-800 hover:bg-gray-300' : 'bg-green-200 text-green-800 hover:bg-green-300' }}">
                            {{ $sound->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        @if(!$sound->is_default)
                            <button wire:click="setDefault({{ $sound->id }})" class="px-3 py-1 text-sm rounded bg-yellow-200 text-yellow-800 hover:bg-yellow-300">
                                Set Default
                            </button>
                        @endif
                        <button wire:click="deleteSound({{ $sound->id }})" onclick="return confirm('Are you sure?')" class="px-3 py-1 text-sm rounded bg-red-200 text-red-800 hover:bg-red-300">
                            Delete
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>