@if($pendingCount > 0)
<div class="bg-white rounded-lg shadow p-4 mb-4">
    <div class="flex items-center justify-between">
        <h3 class="font-medium text-gray-900">Pending Moderation</h3>
        <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
            {{ $pendingCount }}
        </span>
    </div>
    <p class="mt-2 text-sm text-gray-600">
        {{ $pendingCount }} content items awaiting review
    </p>
    <div class="mt-4">
        <a href="{{ route('moderation.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
            Review now →
        </a>
    </div>
</div>
@endif
