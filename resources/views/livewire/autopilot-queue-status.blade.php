<div>
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-medium">Autopilot Queue Status</h3>
        <div class="flex gap-2">
            <select wire:model="filterStatus" class="text-sm border rounded px-2 py-1">
                <option value="all">All Tasks</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="completed">Completed</option>
                <option value="failed">Failed</option>
            </select>
            @if($failedCount > 0)
                <button wire:click="retryFailedTasks"
                        class="text-sm bg-red-100 hover:bg-red-200 text-red-800 px-3 py-1 rounded">
                    Retry All Failed
                </button>
            @endif
        </div>
    </div>
    
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg cursor-pointer hover:bg-blue-100 transition"
             wire:click="applyFilter('pending')">
            <p class="text-sm text-blue-800">Pending</p>
            <p class="text-2xl font-bold text-blue-600">{{ $pendingCount }}</p>
        </div>
        <div class="bg-yellow-50 p-4 rounded-lg cursor-pointer hover:bg-yellow-100 transition"
             wire:click="applyFilter('processing')">
            <p class="text-sm text-yellow-800">Processing</p>
            <p class="text-2xl font-bold text-yellow-600">{{ $processingCount }}</p>
        </div>
        <div class="bg-green-50 p-4 rounded-lg cursor-pointer hover:bg-green-100 transition"
             wire:click="applyFilter('completed')">
            <p class="text-sm text-green-800">Completed</p>
            <p class="text-2xl font-bold text-green-600">{{ $completedCount }}</p>
        </div>
        <div class="bg-red-50 p-4 rounded-lg cursor-pointer hover:bg-red-100 transition"
             wire:click="applyFilter('failed')">
            <p class="text-sm text-red-800">Failed</p>
            <p class="text-2xl font-bold text-red-600">{{ $failedCount }}</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setInterval(() => {
            Livewire.emit('taskUpdated');
        }, 5000);
    });
</script>
@endpush