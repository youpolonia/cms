<div>
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">
            @if($editingId)
                Edit Scheduled Export
            @else
                Create New Scheduled Export
            @endif
        </h2>
        <form wire:submit.prevent="{{ $editingId ? 'updateSchedule' : 'saveSchedule' }}">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Template Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Template</label>
                    <select wire:model="templateId" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        <option value="">Select a template</option>
                        @foreach($templates as $template)
                        <option value="{{ $template->id }}">{{ $template->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Toggle -->
                @if($editingId)
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <div class="mt-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="isActive" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="ml-2">Active</span>
                        </label>
                    </div>
                </div>
                @endif

                <!-- ... (keep existing form fields) ... -->

                <div class="md:col-span-2 flex justify-between">
                    @if($editingId)
                        <button wire:click="cancelEdit" type="button" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                            Cancel
                        </button>
                    @endif
                    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                        {{ $editingId ? 'Update' : 'Save' }} Schedule
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div>
        <h2 class="text-xl font-semibold mb-4">Active Schedules</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Template</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($schedules as $schedule)
                    <tr>
                        <td class="px-6 py-4">{{ $schedule->template->name }}</td>
                        <td class="px-6 py-4">
                            <button wire:click="toggleStatus({{ $schedule->id }})" class="px-2 py-1 text-xs rounded-full {{ $schedule->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $schedule->is_active ? 'Active' : 'Inactive' }}
                            </button>
                        </td>
                        <td class="px-6 py-4 flex space-x-2">
                            <button wire:click="editSchedule({{ $schedule->id }})" class="text-blue-500 hover:text-blue-700">
                                Edit
                            </button>
                            <button wire:click="confirmDeletion({{ $schedule->id }})" class="text-red-500 hover:text-red-700">
                                Delete
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Delete Confirmation Modal -->
        @if($confirmingDeletionId)
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-medium mb-4">Confirm Deletion</h3>
                <p class="mb-6">Are you sure you want to delete this scheduled export?</p>
                <div class="flex justify-end space-x-3">
                    <button wire:click="$set('confirmingDeletionId', null)" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                        Cancel
                    </button>
                    <button wire:click="deleteSchedule" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                        Delete
                    </button>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>