<div>
    @livewire('error-details-modal')

    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
        <h2 class="text-xl font-semibold">Export History</h2>
        
        <div class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <input wire:model.debounce.300ms="search" type="text" placeholder="Search templates..." 
                   class="px-3 py-2 border rounded-md shadow-sm">

            <select wire:model="statusFilter" class="px-3 py-2 border rounded-md shadow-sm">
                <option value="">All Statuses</option>
                <option value="success">Success</option>
                <option value="failed">Failed</option>
                <option value="partial">Partial</option>
            </select>

            <select wire:model="scheduleFilter" class="px-3 py-2 border rounded-md shadow-sm">
                <option value="">All Schedules</option>
                @foreach(App\Models\ScheduledExport::all() as $schedule)
                <option value="{{ $schedule->id }}">{{ $schedule->template->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th wire:click="sortBy('template_id')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Template
                        @if($sortField === 'template_id')
                            @if($sortDirection === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('status')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Status
                        @if($sortField === 'status')
                            @if($sortDirection === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        @endif
                    </th>
                    <th wire:click="sortBy('started_at')" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer">
                        Date
                        @if($sortField === 'started_at')
                            @if($sortDirection === 'asc')
                                ↑
                            @else
                                ↓
                            @endif
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Duration
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        File Size
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($history as $record)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $record->template->name }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($record->status === 'success') bg-green-100 text-green-800
                            @elseif($record->status === 'failed') bg-red-100 text-red-800
                            @else bg-yellow-100 text-yellow-800 @endif">
                            {{ ucfirst($record->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $record->started_at->format('M j, Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $record->duration }}s
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $record->file_size_formatted }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap flex gap-2">
                        @if($record->status === 'success')
                        <a href="{{ route('exports.download', $record->id) }}" 
                           class="text-blue-500 hover:text-blue-700">
                            Download
                        </a>
                        @endif
                        @if($record->status !== 'success')
                        <button wire:click="$emit('showErrorDetails', {{ $record->id }})" 
                                class="text-blue-500 hover:text-blue-700">
                            View Error
                        </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $history->links() }}
    </div>
</div>