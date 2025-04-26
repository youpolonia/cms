<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
        Content Schedule
    </h3>

    <div x-data="{ activeTab: 'publishing' }" class="space-y-4">
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'publishing'" 
                    :class="activeTab === 'publishing' ? 'border-blue-500 text-blue-600 dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Upcoming Publications
                </button>
                <button @click="activeTab = 'expirations'" 
                    :class="activeTab === 'expirations' ? 'border-blue-500 text-blue-600 dark:text-blue-400 dark:border-blue-400' : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-600'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Upcoming Expirations
                </button>
            </nav>
        </div>

        <div x-show="activeTab === 'publishing'" class="space-y-3">
            @forelse($upcomingPublications as $content)
                <div class="flex items-start">
                    <div class="flex-shrink-0 pt-0.5">
                        @if($content->is_recurring)
                            <svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        @else
                            <svg class="h-5 w-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $content->title }}
                            @if($content->is_recurring)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    Recurring ({{ $content->recurring_frequency }})
                                </span>
                            @endif
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $content->publish_at->diffForHumans() }} ({{ $content->publish_at->format('M j, Y g:i A') }})
                            @if($content->is_recurring && $content->recurring_end)
                                <span class="block text-xs text-gray-400 dark:text-gray-500">
                                    Ends: {{ $content->recurring_end->format('M j, Y') }}
                                </span>
                            @endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming publications</p>
            @endforelse
        </div>

        <div x-show="activeTab === 'expirations'" class="space-y-3">
            @forelse($upcomingExpirations as $content)
                <div class="flex items-start">
                    <div class="flex-shrink-0 pt-0.5">
                        <svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ $content->title }}
                        </p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $content->expire_at->diffForHumans() }} ({{ $content->expire_at->format('M j, Y g:i A') }})
                        </p>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">No upcoming expirations</p>
            @endforelse
        </div>
    </div>
</div>
