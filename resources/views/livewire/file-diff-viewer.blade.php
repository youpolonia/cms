@php
    $fileExtension = pathinfo($diff['file'] ?? '', PATHINFO_EXTENSION);
    $languageMap = [
        'php' => 'php',
        'js' => 'javascript',
        'css' => 'css',
        'html' => 'html',
        'blade.php' => 'html',
        'json' => 'json'
    ];
    $language = $languageMap[$fileExtension] ?? 'plaintext';
@endphp

<div class="diff-viewer-container bg-white rounded-lg shadow overflow-hidden">
    <div class="diff-toolbar bg-gray-100 px-4 py-2 border-b flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
        <div class="file-info">
            <span class="font-mono text-sm">{{ $diff['file'] ?? '' }}</span>
            <div class="text-xs text-gray-500 mt-1">
                @if(isset($diff['stats']))
                    <span class="inline-block mr-3">
                        <span class="text-green-600">+{{ $diff['stats']['added'] ?? 0 }}</span> 
                        <span class="text-red-600">-{{ $diff['stats']['removed'] ?? 0 }}</span>
                    </span>
                    <span class="inline-block">
                        Size: {{ number_format(($diff['stats']['size_change'] ?? 0) / 1024, 2) }} KB
                    </span>
                @endif
            </div>
        </div>
        <div class="diff-controls flex flex-wrap gap-2">
            <button wire:click="saveChanges" 
                    wire:loading.attr="disabled"
                    class="px-3 py-1 text-xs bg-green-500 text-white hover:bg-green-600 rounded">
                <span wire:loading.remove>Save Changes</span>
                <span wire:loading>Saving...</span>
            </button>
            
            <div class="bulk-actions flex items-center space-x-2">
                <select wire:model="bulkAction" class="text-xs border rounded px-2 py-1">
                    <option value="none">Bulk Actions</option>
                    <option value="accept-all">Accept All Changes</option>
                    <option value="reject-all">Reject All Changes</option>
                </select>
                <button wire:click="applyBulkAction" class="px-2 py-1 text-xs bg-blue-500 text-white hover:bg-blue-600 rounded">
                    Apply
                </button>
            </div>
            <button wire:click="toggleViewMode" class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">
                {{ $viewMode === 'side-by-side' ? 'Inline View' : 'Side-by-Side' }}
            </button>
            <button wire:click="toggleSyntaxHighlighting" class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">
                {{ $syntaxHighlighting ? 'Disable Syntax' : 'Enable Syntax' }}
            </button>
            <button wire:click="toggleLineNumbers" class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">
                {{ $showLineNumbers ? 'Hide Numbers' : 'Show Numbers' }}
            </button>
            <button wire:click="toggleHighlightMode" class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">
                {{ $highlightMode === 'word' ? 'Line Mode' : 'Word Mode' }}
            </button>
            <button wire:click="toggleWhitespaceChanges" class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">
                {{ $showWhitespaceChanges ? 'Hide Whitespace' : 'Show Whitespace' }}
            </button>
        </div>
    </div>

    @if($viewMode === 'side-by-side')
        <div class="side-by-side-diff grid grid-cols-1 md:grid-cols-2 divide-x">
            <div class="old-version p-2 overflow-auto">
                <div class="version-header bg-red-50 text-red-800 px-2 py-1 text-sm font-medium">
                    Version {{ $diff['old_version'] ?? '' }}
                </div>
                <pre class="mt-2"><code class="language-{{ $language }}">@foreach(explode("\n", $diff['old_content'] ?? '') as $i => $line)
@if($showLineNumbers)
<span class="text-gray-500 mr-2">{{ $i+1 }}</span>
@endif
@if(isset($resolvedLines[$i+1]))
<span class="{{ $resolvedLines[$i+1] === 'accept' ? 'bg-green-100' : 'bg-red-100' }}">{{ $line }}</span>
@else
{{ $line }}
@endif
@endforeach</code></pre>
            </div>
            <div class="new-version p-2 overflow-auto">
                <div class="version-header bg-green-50 text-green-800 px-2 py-1 text-sm font-medium">
                    Version {{ $diff['new_version'] ?? '' }}
                </div>
                <pre class="mt-2"><code class="language-{{ $language }}">@foreach(explode("\n", $diff['new_content'] ?? '') as $i => $line)
@if($showLineNumbers)
<span class="text-gray-500 mr-2">{{ $i+1 }}</span>
@endif
@if(isset($resolvedLines[$i+1]))
<span class="{{ $resolvedLines[$i+1] === 'accept' ? 'bg-green-100' : 'bg-red-100' }}">{{ $line }}</span>
@else
{{ $line }}
@endif
@endforeach</code></pre>
            </div>
        </div>
    @else
        <div class="inline-diff p-2 overflow-auto" x-data="{ hoverLine: null }">
            <pre class="diff-content"><code class="language-diff">@foreach($diff['diff'] ?? [] as $line)
@if($showLineNumbers && $line['type'] !== 'context')
<span class="text-gray-500 mr-2">{{ $line['line_number'] }}</span>
@endif
@if($line['type'] === 'added')
<div class="flex items-start group">
    <div class="flex space-x-1 mr-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <button wire:click="$emit('resolve-conflict', {line: {{ $line['line_number'] }}, action: 'accept'})" 
                class="px-1 text-xs bg-green-500 text-white rounded hover:bg-green-600">
            ✓
        </button>
        <button wire:click="$emit('resolve-conflict', {line: {{ $line['line_number'] }}, action: 'reject'})" 
                class="px-1 text-xs bg-red-500 text-white rounded hover:bg-red-600">
            ✕
        </button>
    </div>
    <span class="{{ isset($resolvedLines[$line['line_number']]) ? ($resolvedLines[$line['line_number']] === 'accept' ? 'bg-green-100 line-applied' : 'bg-red-100 line-rejected') : '' }} 
                  {{ $highlightMode === 'word' ? 'diff-word-highlight' : 'diff-line-highlight' }}">
        +{{ $line['line'] }}
    </span>
</div>
@elseif($line['type'] === 'removed')
<div class="flex items-start group">
    <div class="flex space-x-1 mr-2 opacity-0 group-hover:opacity-100 transition-opacity">
        <button wire:click="$emit('resolve-conflict', {line: {{ $line['line_number'] }}, action: 'accept'})" 
                class="px-1 text-xs bg-green-500 text-white rounded hover:bg-green-600">
            ✓
        </button>
        <button wire:click="$emit('resolve-conflict', {line: {{ $line['line_number'] }}, action: 'reject'})" 
                class="px-1 text-xs bg-red-500 text-white rounded hover:bg-red-600">
            ✕
        </button>
    </div>
    <span class="{{ isset($resolvedLines[$line['line_number']]) ? ($resolvedLines[$line['line_number']] === 'accept' ? 'bg-green-100 line-applied' : 'bg-red-100 line-rejected') : '' }} 
                  {{ $highlightMode === 'word' ? 'diff-word-highlight' : 'diff-line-highlight' }}">
        -{{ $line['line'] }}
    </span>
</div>
@else 
{{ $line['line'] }}
@endif
@endforeach</code></pre>
        </div>
    @endif
</div>

@push('styles')
<style>
    .diff-viewer-container {
        max-height: 70vh;
    }
    .side-by-side-diff {
        height: calc(70vh - 80px);
    }
    .inline-diff {
        height: calc(70vh - 80px);
    }
    pre {
        margin: 0;
        white-space: pre-wrap;
    }
    .diff-content .added {
        background-color: rgba(74, 222, 128, 0.15);
        border-left: 3px solid rgba(74, 222, 128, 0.9);
        margin-left: -3px;
        padding-left: 3px;
        position: relative;
    }
    .diff-content .added::before {
        content: '+';
        position: absolute;
        left: -20px;
        color: rgba(74, 222, 128, 0.9);
    }
    .diff-content .removed {
        background-color: rgba(248, 113, 113, 0.15);
        border-left: 3px solid rgba(248, 113, 113, 0.9);
        margin-left: -3px;
        padding-left: 3px;
        text-decoration: line-through;
        opacity: 0.9;
        position: relative;
    }
    .diff-content .removed::before {
        content: '-';
        position: absolute;
        left: -20px;
        color: rgba(248, 113, 113, 0.9);
    }
    .diff-word-highlight {
        background-color: rgba(253, 230, 138, 0.5);
        border-radius: 2px;
        padding: 0 2px;
    }
    .diff-line-highlight {
        background-color: rgba(253, 230, 138, 0.3);
        border-left: 3px solid rgba(253, 230, 138, 0.8);
        margin-left: -3px;
        padding-left: 3px;
    }
    .side-by-side-diff {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
    }
    .side-by-side-diff > div {
        overflow-x: auto;
    }
    .version-header {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .diff-content .group:hover {
        background-color: rgba(243, 244, 246, 0.5);
    }
    .diff-line-number {
        display: inline-block;
        width: 40px;
        text-align: right;
        padding-right: 8px;
        color: #6b7280;
        user-select: none;
    }
    .diff-content .action-buttons {
        transition: opacity 0.2s ease;
    }
    .diff-content .added .bg-green-100,
    .diff-content .removed .bg-green-100 {
        background-color: rgba(134, 239, 172, 0.5);
    }
    .diff-content .added .bg-red-100,
    .diff-content .removed .bg-red-100 {
        background-color: rgba(252, 165, 165, 0.5);
    }
</style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('livewire:load', function() {
            Livewire.on('save-success', message => {
                alert(message);
            });
            
            Livewire.on('save-error', message => {
                alert('Error: ' + message);
            });
        });
    </script>
@endpush

@push('scripts')
@if($syntaxHighlighting)
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/default.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>
        document.addEventListener('livewire:load', function() {
            hljs.highlightAll();
        });
        document.addEventListener('livewire:update', function() {
            hljs.highlightAll();
        });
    </script>
@endif
@endpush
