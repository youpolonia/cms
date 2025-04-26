<div>
    <div class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">Notification Filters</h2>
        <p class="mt-1 text-sm text-gray-600">Manage your notification filters to customize what you see in your archive.</p>
    </div>

    <div class="space-y-4">
        @foreach($filters as $index => $filter)
            <div class="p-4 bg-white rounded-lg shadow">
                <div class="flex items-center justify-between">
                    <div class="w-full">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="filter-name-{{ $index }}" value="Filter Name" />
                                <x-text-input 
                                    id="filter-name-{{ $index }}" 
                                    wire:model="filters.{{ $index }}.name" 
                                    type="text" 
                                    class="mt-1 block w-full" 
                                />
                            </div>
                            <div>
                                <x-input-label for="filter-type-{{ $index }}" value="Filter Type" />
                                <select 
                                    id="filter-type-{{ $index }}" 
                                    wire:model="filters.{{ $index }}.type" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                >
                                    @foreach(\App\Models\NotificationFilter::availableFilterTypes() as $type => $options)
                                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="filter-value-{{ $index }}" value="Filter Value" />
                                <x-text-input 
                                    id="filter-value-{{ $index }}" 
                                    wire:model="filters.{{ $index }}.value" 
                                    type="text" 
                                    class="mt-1 block w-full" 
                                />
                            </div>
                            <div class="flex items-center">
                                <x-input-label for="filter-active-{{ $index }}" value="Active" class="mr-2" />
                                <input 
                                    id="filter-active-{{ $index }}" 
                                    wire:model="filters.{{ $index }}.is_active" 
                                    type="checkbox" 
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                >
                            </div>
                        </div>
                    </div>
                    <button 
                        wire:click="deleteFilter({{ $index }})" 
                        class="ml-4 text-red-600 hover:text-red-900"
                    >
                        Delete
                    </button>
                </div>
                <div class="mt-4 flex justify-end">
                    <x-primary-button wire:click="updateFilter({{ $index }})">
                        Save Changes
                    </x-primary-button>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8 p-4 bg-white rounded-lg shadow">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Add New Filter</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <x-input-label for="new-filter-name" value="Filter Name" />
                <x-text-input 
                    id="new-filter-name" 
                    wire:model="newFilter.name" 
                    type="text" 
                    class="mt-1 block w-full" 
                />
            </div>
            <div>
                <x-input-label for="new-filter-type" value="Filter Type" />
                <select 
                    id="new-filter-type" 
                    wire:model="newFilter.type" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                >
                    @foreach(\App\Models\NotificationFilter::availableFilterTypes() as $type => $options)
                        <option value="{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="new-filter-value" value="Filter Value" />
                <x-text-input 
                    id="new-filter-value" 
                    wire:model="newFilter.value" 
                    type="text" 
                    class="mt-1 block w-full" 
                />
            </div>
            <div class="flex items-center">
                <x-input-label for="new-filter-active" value="Active" class="mr-2" />
                <input 
                    id="new-filter-active" 
                    wire:model="newFilter.is_active" 
                    type="checkbox" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    checked
                >
            </div>
        </div>
        <div class="mt-4 flex justify-end">
            <x-primary-button wire:click="addFilter">
                Add Filter
            </x-primary-button>
        </div>
    </div>
</div>