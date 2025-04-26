<div>
    <!-- Task details modal -->
    <div x-show="$wire.showModal"
         x-transition
         class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
            <div class="p-6">
                <div class="flex justify-between items-start">
                    <h3 class="text-lg font-medium">Task Details</h3>
                    <button wire:click="closeModal"
                            class="text-gray-500 hover:text-gray-700"
                            aria-label="Close modal">
                        &times;
                    </button>
                </div>

                <div class="mt-4 space-y-4">
                    @if(isset($selectedTask) && $selectedTask)
                        <div>
                            <p class="text-sm text-gray-500">Task ID</p>
                            <p>{{ $selectedTask?->id ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="capitalize">{{ $selectedTask?->status ?? 'unknown' }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Created At</p>
                            <p>{{ $selectedTask?->created_at ?? 'N/A' }}</p>
                        </div>
                        @if($selectedTask?->status === 'failed')
                        <div>
                            <p class="text-sm text-gray-500">Error</p>
                            <pre class="bg-gray-100 p-2 rounded text-sm overflow-auto max-h-40">{{ $selectedTask?->error_message ?? 'No error details' }}</pre>
                        </div>
                        <button wire:click="retryTask('{{ $selectedTask?->id }}')"
                                class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                            Retry This Task
                        </button>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <p class="text-gray-500">No task selected</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('taskUpdated', () => {
            // Refresh any task details if modal is open
            const component = Livewire.find('autopilot-task-manager');
            if (component.showModal && component.selectedTask) {
                component.refreshTasks();
            }
        });

        // Handle modal show/hide events
        Livewire.on('showModal', (taskId = null) => {
            const component = Livewire.find('autopilot-task-manager');
            if (taskId) {
                component.showTaskDetails(taskId);
            } else {
                component.showModal = true;
            }
        });
    });
</script>
@endpush
