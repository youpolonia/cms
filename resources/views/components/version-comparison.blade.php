<div
    class="version-comparison-container bg-white rounded-lg shadow overflow-hidden"
    data-old-content="{{ $oldVersion->content }}"
    data-new-content="{{ $newVersion->content }}"
    data-is-old-autosave="{{ $oldVersion->is_autosave ? 'true' : 'false' }}"
    data-is-new-autosave="{{ $newVersion->is_autosave ? 'true' : 'false' }}"
    data-version-id="{{ $oldVersion->id }}"
    data-compared-version-id="{{ $newVersion->id }}"
>
    <div class="grid grid-cols-2 divide-x divide-gray-200">
        <!-- Old Version -->
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-medium">
                    Version #{{ $oldVersion->version_number }}
                    @if($oldVersion->is_autosave)
                        <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Autosave</span>
                    @endif
                </h3>
                <span class="text-sm text-gray-500">
                    {{ $oldVersion->created_at->format('M j, Y H:i') }}
                </span>
            </div>
            
            <div class="version-content bg-gray-50 p-3 rounded-md font-mono text-sm overflow-auto max-h-96">
                @foreach(explode("\n", $oldVersion->content) as $i => $line)
                    @php
                        $diff = collect($contentDiffs)->firstWhere('line', $i + 1);
                    @endphp
                    <div class="flex @if($diff) bg-red-50 @endif">
                        <span class="text-gray-500 w-8 pr-2 text-right">{{ $i + 1 }}</span>
                        <span class="@if($diff) text-red-600 line-through @endif">
                            {{ $line }}
                        </span>
                    </div>
                    @if($diff && $diff['new'] !== $diff['old'])
                        <div class="flex bg-green-50">
                            <span class="text-gray-500 w-8 pr-2 text-right"></span>
                            <span class="text-green-600">
                                {{ $diff['new'] }}
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- New Version -->
        <div class="p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-medium">
                    Version #{{ $newVersion->version_number }}
                    @if($newVersion->is_autosave)
                        <span class="ml-2 text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">Autosave</span>
                    @endif
                </h3>
                <span class="text-sm text-gray-500">
                    {{ $newVersion->created_at->format('M j, Y H:i') }}
                </span>
            </div>

            <div class="version-content bg-gray-50 p-3 rounded-md font-mono text-sm overflow-auto max-h-96">
                @foreach(explode("\n", $newVersion->content) as $i => $line)
                    @php
                        $diff = collect($contentDiffs)->firstWhere('line', $i + 1);
                    @endphp
                    <div class="flex @if($diff) bg-green-50 @endif">
                        <span class="text-gray-500 w-8 pr-2 text-right">{{ $i + 1 }}</span>
                        <span class="@if($diff) text-green-600 @endif">
                            {{ $line }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Diff Summary -->
    <div class="border-t border-gray-200 p-4 bg-gray-50">
        <h4 class="font-medium mb-2">Changes Summary</h4>
        <div class="grid grid-cols-4 gap-2 text-sm">
            <div class="bg-red-50 text-red-700 p-2 rounded">
                Changed Lines: {{ count($contentDiffs) }}
            </div>
            <div class="bg-blue-50 text-blue-700 p-2 rounded">
                Word Difference: {{ $wordCountDiff }}
            </div>
            <div class="bg-purple-50 text-purple-700 p-2 rounded">
                Character Difference: {{ $characterCountDiff }}
            </div>
            <div class="bg-gray-100 text-gray-700 p-2 rounded">
                Compared: {{ $comparedAt }}
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .version-content {
        line-height: 1.5;
    }
    .version-content div {
        white-space: pre-wrap;
    }
    .diff-added {
        background-color: #f0fff4;
        color: #38a169;
    }
    .diff-removed {
        background-color: #fff5f5;
        color: #e53e3e;
    }
    .diff-modified {
        background-color: #fffaeb;
        color: #dd6b20;
    }
    .diff-line-number {
        color: #718096;
        width: 8px;
        padding-right: 2px;
        text-align: right;
    }
</style>
@endpush