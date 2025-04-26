<div class="p-6 bg-white rounded-lg shadow">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Approval Analytics</h2>
        <button wire:click="refresh" 
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
            Refresh Data
        </button>
    </div>

    @if($loading)
        <div class="text-center py-8">
            <x-spinner class="w-8 h-8 mx-auto" />
            <p class="mt-2 text-gray-600">Loading analytics...</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Stats Cards -->
            <div class="bg-blue-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-blue-800">Pending</h3>
                <p class="text-3xl font-bold">{{ $stats['pending'] ?? 0 }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-green-800">Approved</h3>
                <p class="text-3xl font-bold">{{ $stats['approved'] ?? 0 }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <h3 class="text-lg font-medium text-red-800">Rejected</h3>
                <p class="text-3xl font-bold">{{ $stats['rejected'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Workflow Progress -->
        <div class="mb-8">
            <h3 class="text-xl font-semibold mb-4">Workflow Progress</h3>
            <div class="bg-white p-4 rounded-lg border">
                <div class="mb-2">
                    <span class="font-medium">Completion Rate:</span>
                    <span class="ml-2">{{ $workflow['completionRate'] ?? 0 }}%</span>
                </div>
                <div class="mb-2">
                    <span class="font-medium">Average Step Time:</span>
                    <span class="ml-2">{{ $workflow['avgStepTime'] ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="font-medium">Slowest Step:</span>
                    <span class="ml-2">{{ $workflow['slowestStep'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div>
            <h3 class="text-xl font-semibold mb-4">Pending Approvals</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 text-left">Content</th>
                            <th class="py-2 px-4 text-left">Current Step</th>
                            <th class="py-2 px-4 text-left">Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pending as $item)
                            <tr class="border-b">
                                <td class="py-2 px-4">{{ $item['content']['title'] ?? 'N/A' }}</td>
                                <td class="py-2 px-4">{{ $item['current_step']['name'] ?? 'N/A' }}</td>
                                <td class="py-2 px-4">{{ \Carbon\Carbon::parse($item['created_at'])->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-4 text-center text-gray-500">No pending approvals</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>