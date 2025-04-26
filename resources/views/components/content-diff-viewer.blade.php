@props(['diff'])

<div class="diff-viewer bg-white rounded-lg shadow overflow-hidden">
    <div class="diff-header bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
        <h3 class="font-medium text-gray-900">Version Comparison</h3>
        <div class="flex items-center gap-2">
            <span class="px-2 py-1 text-xs font-semibold rounded-full
                        {{ $similarity > 80 ? 'bg-green-100 text-green-800' :
                           ($similarity > 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                {{ $similarity }}% Similar
            </span>
            @if($hasFieldChanges() && $isLineDiff())
                <button x-data="{ showLines: false }"
                        @click="showLines = !showLines"
                        class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded-full hover:bg-blue-200 transition">
                    <span x-show="!showLines">Show Line Diffs</span>
                    <span x-show="showLines">Show Field Diffs</span>
                </button>
            @endif
        </div>
    </div>

    <div class="diff-content divide-y" x-data="{ showLines: false }">
        @if($isLineDiff())
            <template x-if="showLines">
                <div class="line-diff-view p-4">
                    {!! $changes['line_html'] ?? '' !!}
                </div>
            </template>
        @endif

        <template x-if="!showLines || !$isLineDiff()">
            @foreach($changes as $change)
            <div class="diff-item px-4 py-3">
                <div class="flex items-center justify-between mb-1">
                    <span class="font-medium text-gray-700">{{ $change['field'] }}</span>
                    <span class="text-xs px-2 py-1 rounded 
                              {{ $change['change_type'] === 'added' ? 'bg-green-100 text-green-800' : 
                                 ($change['change_type'] === 'removed' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800') }}">
                        {{ ucfirst($change['change_type']) }}
                    </span>
                </div>

                @if($change['change_type'] === 'modified')
                    <div class="grid grid-cols-2 gap-4">
                        <div class="old-value bg-gray-50 p-2 rounded">
                            <div class="text-xs text-gray-500 mb-1">Old Value</div>
                            <div class="text-gray-800">{{ $change['old_value'] }}</div>
                        </div>
                        <div class="new-value bg-gray-50 p-2 rounded">
                            <div class="text-xs text-gray-500 mb-1">New Value</div>
                            <div class="text-gray-800">{{ $change['new_value'] }}</div>
                        </div>
                    </div>
                @else
                    <div class="value bg-gray-50 p-2 rounded mt-1">
                        <div class="text-gray-800">
                            {{ $change['change_type'] === 'added' ? $change['new_value'] : $change['old_value'] }}
                        </div>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>