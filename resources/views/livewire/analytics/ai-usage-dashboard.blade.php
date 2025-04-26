<div>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">AI Usage Analytics</h2>
        <div class="flex space-x-2">
            <button wire:click="updateTimeframe('today')"
                class="px-3 py-1 rounded {{ $timeframe === 'today' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Today
            </button>
            <button wire:click="updateTimeframe('week')"
                class="px-3 py-1 rounded {{ $timeframe === 'week' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Week
            </button>
            <button wire:click="updateTimeframe('month')"
                class="px-3 py-1 rounded {{ $timeframe === 'month' ? 'bg-blue-500 text-white' : 'bg-gray-200' }}">
                Month
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-2">Total AI Uses</h3>
            <p class="text-3xl font-bold">{{ number_format($metrics['total_usage']) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-2">Avg Per User</h3>
            <p class="text-3xl font-bold">{{ round($metrics['avg_per_user'], 1) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-2">Active Users</h3>
            <p class="text-3xl font-bold">{{ $metrics['active_users'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Your Usage Stats</h3>
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-gray-500">Total Usage</p>
                    <p class="text-xl font-bold">{{ $userStats['total_usage'] }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Last Used</p>
                    <p class="text-xl font-bold">{{ $userStats['last_used'] ? $userStats['last_used']->diffForHumans() : 'Never' }}</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    @foreach($userStats['usage_by_type'] as $type => $count)
                    <div>
                        <p class="text-sm text-gray-500 capitalize">{{ $type }}</p>
                        <p class="text-lg font-bold">{{ $count }}</p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="font-semibold text-gray-700 mb-4">Your Rate Limits</h3>
            <div class="space-y-4">
                @foreach($rateLimits as $type => $limit)
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="capitalize">{{ $type }}</span>
                        <span class="font-bold">
                            {{ $limit['remaining'] }} / 5 remaining
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full"
                             style="width: {{ ($limit['remaining'] / 5) * 100 }}%"></div>
                    </div>
                    @if($limit['available_in'] > 0)
                    <p class="text-xs text-gray-500 mt-1">
                        Resets in {{ $limit['available_in'] }} seconds
                    </p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h3 class="font-semibold text-gray-700 mb-4">Top AI Users</h3>
        <div class="space-y-3">
            @foreach($topUsers as $user)
            <div class="flex justify-between items-center">
                <div>
                    <p class="font-medium">{{ $user['name'] }}</p>
                    <p class="text-sm text-gray-500">
                        Last used {{ $user['last_ai_used_at']->diffForHumans() }}
                    </p>
                </div>
                <span class="font-bold">{{ $user['ai_usage_count'] }} uses</span>
            </div>
            @endforeach
        </div>
    </div>
</div>
