@props([
    'steps' => [],
    'currentStep' => null,
    'completedSteps' => [],
    'rejectedSteps' => [],
    'timeMetrics' => [],
    'notificationSettings' => []
])

<div class="approval-timeline">
    <div class="relative">
        <!-- Timeline metrics summary -->
        @if(!empty($timeMetrics))
            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="text-sm font-medium text-gray-900 mb-2">Approval Metrics</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Avg. Time</p>
                        <p class="text-lg font-semibold">{{ $timeMetrics['average_time'] ?? 'N/A' }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Fastest</p>
                        <p class="text-lg font-semibold">{{ $timeMetrics['fastest_time'] ?? 'N/A' }}</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xs text-gray-500">Slowest</p>
                        <p class="text-lg font-semibold">{{ $timeMetrics['slowest_time'] ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        @endif

        @if(!empty($notificationSettings))
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <h3 class="text-sm font-medium text-blue-900 mb-2">Notification Settings</h3>
                <div class="space-y-2">
                    @foreach($notificationSettings as $setting)
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   id="notification-{{ $setting['type'] }}" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                   @if($setting['enabled']) checked @endif
                                   disabled>
                            <label for="notification-{{ $setting['type'] }}" class="ml-2 block text-sm text-gray-700">
                                {{ $setting['label'] }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Timeline line -->
        <div class="absolute left-4 top-0 h-full w-0.5 bg-gray-200"></div>

        <!-- Timeline steps -->
        <div class="space-y-8">
            @foreach($steps as $index => $step)
                @php
                    $isCurrent = $currentStep === $step['id'];
                    $isCompleted = in_array($step['id'], $completedSteps);
                    $isRejected = in_array($step['id'], $rejectedSteps);
                    
                    $statusClass = $isCurrent ? 'bg-blue-500 border-blue-500' : 
                                ($isCompleted ? 'bg-green-500 border-green-500' : 
                                ($isRejected ? 'bg-red-500 border-red-500' : 'bg-gray-200 border-gray-300'));
                @endphp

                <div class="relative flex items-start group">
                    <!-- Step indicator -->
                    <div class="flex items-center h-9">
                        <div class="relative z-10 w-8 h-8 flex items-center justify-center rounded-full border-2 {{ $statusClass }} text-white">
                            @if($isCompleted)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @elseif($isRejected)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                    </div>

                    <!-- Step content -->
                    <div class="ml-4 flex-1 pt-1">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-medium {{ $isCurrent ? 'text-blue-600' : 'text-gray-900' }}">
                                {{ $step['name'] }}
                            </h3>
                            <span class="text-xs text-gray-500">
                                @if($step['completed_at'])
                                    {{ \Carbon\Carbon::parse($step['completed_at'])->diffForHumans() }}
                                @elseif($step['started_at'])
                                    Started {{ \Carbon\Carbon::parse($step['started_at'])->diffForHumans() }}
                                @endif
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            {{ $step['description'] }}
                        </p>

                        @if($isRejected && $step['rejection_reason'])
                            <div class="mt-2 p-2 bg-red-50 rounded text-sm text-red-700">
                                {{ $step['rejection_reason'] }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
