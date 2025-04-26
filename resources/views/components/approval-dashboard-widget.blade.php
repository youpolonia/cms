<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">
                Theme Approvals
                @if($hasUnreadNotifications)
                    <span class="ml-2 inline-flex items-center justify-center w-4 h-4 rounded-full bg-red-500 text-white text-xs"></span>
                @endif
            </h3>
            
            <div class="flex space-x-2">
                <button class="px-2 py-1 text-xs bg-blue-500 hover:bg-blue-600 text-white rounded" wire:click="filter('pending')">
                    Pending ({{ $approvalStats['pending'] }})
                </button>
                <button class="px-2 py-1 text-xs bg-green-500 hover:bg-green-600 text-white rounded" wire:click="filter('approved')">
                    Approved ({{ $approvalStats['approved'] }})
                </button>
                <button class="px-2 py-1 text-xs bg-red-500 hover:bg-red-600 text-white rounded" wire:click="filter('rejected')">
                    Rejected ({{ $approvalStats['rejected'] }})
                </button>
            </div>
        </div>

        <div class="mb-4">
            <div class="flex justify-between items-center mb-1">
                <span class="text-sm font-medium">Workflow Progress</span>
                <span class="text-xs text-gray-500">
                    Step {{ round($workflowProgress['completedSteps']) }} of {{ $workflowProgress['totalSteps'] }}
                </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ ($workflowProgress['completedSteps'] / $workflowProgress['totalSteps']) * 100 }}%"></div>
            </div>
            
            <div class="mt-2 grid grid-cols-3 gap-2 text-xs">
                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded">
                    <div class="text-gray-500 dark:text-gray-400">Avg Step Time</div>
                    <div>{{ $workflowProgress['avgStepTime'] }}</div>
                </div>
                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded">
                    <div class="text-gray-500 dark:text-gray-400">Slowest Step</div>
                    <div>{{ $workflowProgress['slowestStep'] }}</div>
                </div>
                <div class="p-2 bg-gray-50 dark:bg-gray-700 rounded">
                    <div class="text-gray-500 dark:text-gray-400">Completion Rate</div>
                    <div>{{ $workflowProgress['completionRate'] }}%</div>
                </div>
            </div>
        </div>

        <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900 rounded-lg">
            <div class="flex items-center justify-between mb-1">
                <span class="text-sm font-medium">Bottlenecks</span>
                <span class="text-xs bg-yellow-500 text-white px-2 py-1 rounded-full">
                    {{ count($approvalStats['bottlenecks']) }}
                </span>
            </div>
            @if(count($approvalStats['bottlenecks']) > 0)
                <ul class="text-xs mt-1 space-y-1">
                    @foreach($approvalStats['bottlenecks'] as $bottleneck)
                        <li class="flex justify-between">
                            <span>{{ $bottleneck['step'] }}</span>
                            <span class="font-medium">{{ $bottleneck['avg_time'] }}</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">No bottlenecks detected</p>
            @endif
        </div>

        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Avg Approval Time</span>
                <span class="text-xs bg-blue-500 text-white px-2 py-1 rounded-full">
                    {{ $approvalStats['avgTime'] }}
                </span>
            </div>
        </div>

        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900 rounded-lg">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium">Your Pending Actions</span>
                <span class="text-xs bg-blue-500 text-white px-2 py-1 rounded-full">
                    {{ $userPendingActions }}
                </span>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($pendingApprovals as $approval)
                <div class="border-b border-gray-200 dark:border-gray-700 pb-3 last:border-0 last:pb-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <h4 class="font-medium">{{ $approval->themeVersion->theme->name }}</h4>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Version {{ $approval->themeVersion->version }}
                            </p>
                            @if($approval->currentStep)
                                <div class="mt-1 text-xs text-blue-500 dark:text-blue-400">
                                    Current Step: {{ $approval->currentStep->name }}
                                </div>
                            @endif
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-2 py-1 text-xs bg-green-500 hover:bg-green-600 text-white rounded" wire:click="approve({{ $approval->id }})">
                                Approve
                            </button>
                            <button class="px-2 py-1 text-xs bg-red-500 hover:bg-red-600 text-white rounded" wire:click="reject({{ $approval->id }})">
                                Reject
                            </button>
                        </div>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Submitted {{ $approval->created_at->diffForHumans() }}
                    </div>
                </div>
            @empty
                <p class="text-gray-500 dark:text-gray-400 text-center py-4">No pending approvals</p>
            @endforelse
        </div>

        <div class="mt-4 flex justify-end">
            <a href="{{ route('theme-approvals.index') }}" class="text-sm text-blue-500 hover:text-blue-700 dark:hover:text-blue-400">
                View All →
            </a>
        </div>
    </div>
</div>
