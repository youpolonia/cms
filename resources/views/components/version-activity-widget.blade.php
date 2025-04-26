<div class="bg-white rounded-lg shadow-md p-6 mb-6">
    <h3 class="text-lg font-semibold mb-4">Recent Version Activity</h3>
    <ul class="space-y-3">
        @foreach($recentActivity as $activity)
        <li class="flex items-start">
            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center">
                <span class="text-gray-500">{{ $activity->user->initials }}</span>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">
                    {{ $activity->user->name }} {{ $activity->action }} version {{ $activity->version->version_number }}
                </p>
                <p class="text-sm text-gray-500">
                    {{ $activity->created_at->diffForHumans() }}
                </p>
            </div>
        </li>
        @endforeach
    </ul>

    <h3 class="text-lg font-semibold mt-6 mb-4">Most Active Content</h3>
    <ul class="space-y-2">
        @foreach($mostActiveContent as $content)
        <li class="flex justify-between">
            <span class="text-sm font-medium text-gray-900 truncate">
                {{ $content->version->content->title }}
            </span>
            <span class="text-sm text-gray-500">
                {{ $content->count }} versions
            </span>
        </li>
        @endforeach
    </ul>
</div>