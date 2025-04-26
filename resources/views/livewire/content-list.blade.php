<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Content Management</h2>
        <div class="flex space-x-4">
            <div x-data="{ showBulkActions: false }" class="relative">
                <button @click="showBulkActions = !showBulkActions" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                        :class="{ 'bg-blue-700': showBulkActions }">
                    Bulk Actions
                </button>
                
                <div x-show="showBulkActions" @click.away="showBulkActions = false"
                     class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10">
                    <div class="p-4 space-y-4">
                        <select wire:model="bulkAction" class="w-full border rounded-md px-3 py-2">
                            <option value="">Select Action</option>
                            <option value="publish">Publish Selected</option>
                            <option value="archive">Archive Selected</option>
                            <option value="delete">Delete Selected</option>
                        </select>
                        <button wire:click="performBulkAction" 
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition"
                                :disabled="!$wire.bulkAction || !count($wire.selected)">
                            Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($batchInProgress)
        <div class="mb-6 bg-blue-50 p-4 rounded-md">
            <div class="flex justify-between items-center mb-2">
                <span class="font-medium">Processing bulk operation...</span>
                <span>{{ $batchProgress }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $batchProgress }}%"></div>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <input type="checkbox" wire:model="selectAll" class="rounded">
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Title
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Last Modified
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Versions
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($contents as $content)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" wire:model="selected" value="{{ $content->id }}" class="rounded">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">{{ $content->title }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{ $content->status === 'published' ? 'bg-green-100 text-green-800' : 
                               ($content->status === 'archived' ? 'bg-gray-100 text-gray-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($content->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $content->updated_at->format('M d, Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $content->versions->count() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($content->versions->count() > 1)
                            <a href="{{ route('content.versions.compare', $content) }}"
                               class="text-blue-600 hover:text-blue-800">
                                Compare Versions
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>