@props([
    'files' => [],
    'selectedFile' => null,
    'theme' => null,
    'version1' => null,
    'version2' => null
])

<div class="file-tree bg-white rounded-lg shadow overflow-hidden">
    <div class="p-4 border-b">
        <h3 class="font-medium">Changed Files</h3>
        <div class="mt-2 relative">
            <input 
                type="text" 
                placeholder="Filter files..." 
                class="w-full px-3 py-1 text-sm border rounded"
                wire:model.debounce.300ms="fileFilter"
            >
        </div>
    </div>
    
    <div class="overflow-y-auto" style="max-height: calc(100vh - 200px)">
        <ul class="divide-y divide-gray-200">
            @foreach($this->filteredFiles as $fileType => $fileGroup)
                <li class="px-4 py-2 bg-gray-50">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium">
                            {{ ucfirst($fileType) }} ({{ count($fileGroup) }})
                        </span>
                    </div>
                </li>
                
                @foreach($fileGroup as $filePath => $fileData)
                    <li 
                        class="px-4 py-2 hover:bg-gray-100 cursor-pointer"
                        :class="{ 'bg-blue-50': selectedFile === '{{ $filePath }}' }"
                        wire:click="$emit('selectFile', '{{ $filePath }}')"
                    >
                        <div class="flex items-center">
                            @php
                                $icon = match(pathinfo($filePath, PATHINFO_EXTENSION)) {
                                    'php' => 'file-code',
                                    'js' => 'file-code',
                                    'css' => 'file-code',
                                    'html' => 'file-code',
                                    'blade.php' => 'file-code',
                                    'json' => 'file-code',
                                    'jpg', 'jpeg', 'png', 'gif' => 'file-image',
                                    default => 'file'
                                };
                            @endphp
                            
                            <x-icon name="{{ $icon }}" class="w-4 h-4 mr-2 text-gray-500" />
                            
                            <span class="truncate">
                                {{ basename($filePath) }}
                            </span>
                            
                            @if($fileData['stats']['added'] > 0)
                                <span class="ml-auto text-xs text-green-600">
                                    +{{ $fileData['stats']['added'] }}
                                </span>
                            @endif
                            
                            @if($fileData['stats']['removed'] > 0)
                                <span class="ml-1 text-xs text-red-600">
                                    -{{ $fileData['stats']['removed'] }}
                                </span>
                            @endif
                        </div>
                        
                        <div class="text-xs text-gray-500 truncate mt-1">
                            {{ dirname($filePath) }}
                        </div>
                    </li>
                @endforeach
            @endforeach
        </ul>
    </div>
</div>

@push('styles')
<style>
    .file-tree {
        min-width: 250px;
        max-width: 350px;
    }
    .file-tree ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .file-tree li {
        transition: background-color 0.2s ease;
    }
</style>
@endpush
