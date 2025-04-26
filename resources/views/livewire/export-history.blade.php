<div>
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex-1 min-w-[200px]">
                <x-input 
                    type="search" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search exports..."
                />
            </div>

            <div class="flex items-center gap-4 flex-wrap">
                <x-select wire:model.live="status" :options="$statusOptions" />
                <x-select wire:model.live="type" :options="$typeOptions" />
                
                <div class="flex items-center gap-2">
                    <x-input type="date" wire:model.live="dateFrom" placeholder="From" />
                    <span>to</span>
                    <x-input type="date" wire:model.live="dateTo" placeholder="To" />
                </div>

                <x-button wire:click="resetFilters" variant="secondary">
                    Reset
                </x-button>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($exports as $export)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $export->name }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ ucfirst($export->export_type) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-badge :variant="$export->status === 'completed' ? 'success' : ($export->status === 'failed' ? 'danger' : 'warning')">
                                {{ ucfirst($export->status) }}
                            </x-badge>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $export->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($export->status === 'completed')
                                <x-export-download :export="$export" />
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            No exports found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $exports->links() }}
        </div>
    </div>
</div>