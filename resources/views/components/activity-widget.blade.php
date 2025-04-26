<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
        Recent Activity
    </h3>

    <div class="space-y-4">
        @foreach($recentActivities as $activity)
            <div class="flex items-start">
                <div class="flex-shrink-0 pt-0.5">
                    @switch($activity['icon'])
                        @case('pencil')
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            @break
                        @case('shield-check')
                            <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                            </svg>
                            @break
                        @case('download')
                            <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            @break
                    @endswitch
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $activity['title'] }}
                        @if(isset($activity['status']))
                            <span class="text-xs ml-2 px-2 py-1 rounded-full 
                                @if($activity['status'] === 'approved') bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-300
                                @elseif($activity['status'] === 'rejected') bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-300
                                @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-300 @endif">
                                {{ $activity['status'] }}
                            </span>
                        @endif
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $activity['user'] }} • {{ $activity['time']->diffForHumans() }}
                    </p>
                </div>
            </div>
        @endforeach
    </div>
</div>
