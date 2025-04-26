@props([
    'totalContent',
    'totalVersions', 
    'recentActivity'
])

<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-lg shadow-md p-4']) }}>
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Content Statistics</h3>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Content</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $totalContent }}</div>
        </div>
        <div class="bg-gray-50 dark:bg-gray-700 p-3 rounded-lg">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Versions</div>
            <div class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $totalVersions }}</div>
        </div>
    </div>

    <div class="mb-4">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recent Activity</h4>
        <div class="space-y-3">
            @foreach($recentActivity as $version)
                <div class="flex items-start">
                    <div class="flex-shrink-0 h-5 w-5 text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            Version {{ $version->version_number }} created
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            {{ $version->created_at->diffForHumans() }} by {{ $version->creator->name }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
