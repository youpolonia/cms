<div class="bg-white rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Approval Statistics</h3>

    <!-- Progress Bar -->
    <div class="mb-6">
        <div class="flex justify-between mb-2">
            <span>Approval Progress</span>
            <span>{{ $completion }}% ({{ $approvals }}/{{ $required }})</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $completion }}%"></div>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <!-- Approval Type -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-500 mb-1">Approval Type</h4>
            <p class="text-xl font-semibold">{{ ucfirst($approvalLogic) }}</p>
            @if($approvalLogic === 'percentage')
                <p class="text-sm text-gray-500">Threshold: {{ $approvalThreshold }}%</p>
            @endif
        </div>

        <!-- Time Metrics -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-500 mb-1">Average Approval Time</h4>
            <p class="text-xl font-semibold">{{ $averageApprovalTime }} minutes</p>
            <p class="text-sm text-gray-500">Previous: {{ $previousApprovalTime }} minutes</p>
        </div>

        <!-- Historical Rate -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-500 mb-1">Historical Approval Rate</h4>
            <p class="text-xl font-semibold">{{ $historicalApprovalRate }}%</p>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Rejections -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-500 mb-1">Rejections</h4>
            <p class="text-xl font-semibold">{{ $rejections }}</p>
        </div>

        <!-- Time Taken -->
        @if($timeTaken)
        <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="font-medium text-gray-500 mb-1">Time in Review</h4>
            <p class="text-xl font-semibold">{{ gmdate('H\h i\m', $timeTaken) }}</p>
        </div>
        @endif
    </div>
</div>
