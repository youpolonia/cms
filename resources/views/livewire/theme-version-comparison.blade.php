@php
    use App\Models\ThemeVersion;
@endphp

<div>
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Compare Theme Versions</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div>
                <label for="version1" class="block text-sm font-medium text-gray-700 mb-1">Version 1</label>
                <select 
                    wire:model="version1" 
                    id="version1"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Select a version</option>
                    @foreach($versions as $version)
                        <option value="{{ $version->id }}">
                            v{{ $version->getSemanticVersion() }} - {{ $version->created_at->format('M d, Y') }}
                            @if($version->wasRolledBack())
                                (Rolled back)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label for="version2" class="block text-sm font-medium text-gray-700 mb-1">Version 2</label>
                <select 
                    wire:model="version2" 
                    id="version2"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    <option value="">Select a version</option>
                    @foreach($versions as $version)
                        <option value="{{ $version->id }}">
                            v{{ $version->getSemanticVersion() }} - {{ $version->created_at->format('M d, Y') }}
                            @if($version->wasRolledBack())
                                (Rolled back)
                            @endif
                            @if($version->is_latest)
                                (Latest)
                            @endif
                            @if($version->previous_version)
                                (Previous: v{{ ThemeVersion::find($version->previous_version)?->getSemanticVersion() }})
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        
        <button
            wire:click="compareVersions"
            wire:loading.attr="disabled"
            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring focus:ring-indigo-300 disabled:opacity-25 transition"
        >
            <span wire:loading.remove>Compare Versions</span>
            <span wire:loading>
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Comparing...
            </span>
        </button>
        
        @error('comparison')
            <div class="mt-4 text-red-600">{{ $message }}</div>
        @enderror
    </div>
    
    @if(!empty($comparisonData))
        <div class="mt-6 bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    Comparison Results: v{{ $comparisonData['version1'] }} vs v{{ $comparisonData['version2'] }}
                </h3>
                <div class="mt-2 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="bg-blue-50 p-3 rounded-lg">
                        <div class="text-sm text-blue-600">Files Changed</div>
                        <div class="text-2xl font-bold">{{ 
                            (count($comparisonData['files']['added'] ?? []) + 
                            count($comparisonData['files']['removed'] ?? []) + 
                            count($comparisonData['files']['modified'] ?? []) 
                        }}</div>
                    </div>
                    <div class="bg-green-50 p-3 rounded-lg">
                        <div class="text-sm text-green-600">Lines Added</div>
                        <div class="text-2xl font-bold">{{ $comparisonData['stats']['lines_added'] ?? 0 }}</div>
                    </div>
                    <div class="bg-red-50 p-3 rounded-lg">
                        <div class="text-sm text-red-600">Lines Removed</div>
                        <div class="text-2xl font-bold">{{ $comparisonData['stats']['lines_removed'] ?? 0 }}</div>
                    </div>
                    <div class="bg-purple-50 p-3 rounded-lg">
                        <div class="text-sm text-purple-600">Size Change</div>
                        <div class="text-2xl font-bold">{{ 
                            ($comparisonData['stats']['size_change'] > 0 ? '+' : '') . 
                            number_format($comparisonData['stats']['size_change'] / 1024, 2) . ' KB' 
                        }}</div>
                    </div>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-5">
                <div class="lg:col-span-1 border-r">
                    <x-theme-file-tree 
                        :files="$comparisonData['files']"
                        :selectedFile="$selectedFile"
                        :theme="$theme"
                        :version1="$version1"
                        :version2="$version2"
                    />
                </div>
                <div class="lg:col-span-1 border-r">
                    <div class="divide-y divide-gray-200">
                        @if(!empty($comparisonData['files']['added']))
                            <div class="px-6 py-4">
                                <h4 class="font-medium text-green-600 mb-2">Added Files ({{ count($comparisonData['files']['added']) }})</h4>
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($comparisonData['files']['added'] as $file => $data)
                                        <li class="cursor-pointer hover:text-blue-600" wire:click="selectedFile = '{{ $file }}'">
                                            {{ $file }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if(!empty($comparisonData['files']['removed']))
                            <div class="px-6 py-4">
                                <h4 class="font-medium text-red-600 mb-2">Removed Files ({{ count($comparisonData['files']['removed']) }})</h4>
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($comparisonData['files']['removed'] as $file => $data)
                                        <li>{{ $file }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        @if(!empty($comparisonData['files']['modified']))
                            <div class="px-6 py-4">
                                <h4 class="font-medium text-blue-600 mb-2">Modified Files ({{ count($comparisonData['files']['modified']) }})</h4>
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach($comparisonData['files']['modified'] as $file => $data)
                                        <li class="cursor-pointer hover:text-blue-600" 
                                            wire:click="selectedFile = '{{ $file }}'"
                                            :class="{ 'text-blue-600 font-medium': selectedFile === '{{ $file }}' }">
                                            {{ $file }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="lg:col-span-3">
                    @if($selectedFile && isset($comparisonData['files']['modified'][$selectedFile]))
                        <div class="p-4">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-medium">{{ $selectedFile }}</h4>
                                <div class="flex space-x-2">
                                    @if($prevFile = $this->getPrevFile($selectedFile))
                                        <button wire:click="selectedFile = '{{ $prevFile }}'" class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">
                                            Previous
                                        </button>
                                    @endif
                                    @if($nextFile = $this->getNextFile($selectedFile))
                                        <button wire:click="selectedFile = '{{ $nextFile }}'" class="px-3 py-1 text-xs bg-gray-200 hover:bg-gray-300 rounded">
                                            Next
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <livewire:file-diff-viewer 
                                :theme="$theme"
                                :version1="$version1"
                                :version2="$version2"
                                :filePath="$selectedFile"
                                key="{{ $selectedFile }}"
                            />
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50">
                <div class="flex justify-between items-center mb-4">
                    <h4 class="font-medium">Changelog Summary</h4>
                    <button 
                        wire:click="$toggle('showRollbackHistory')"
                        class="text-sm text-indigo-600 hover:text-indigo-800"
                    >
                        {{ $showRollbackHistory ? 'Hide' : 'Show' }} Rollback History
                    </button>
                </div>
                <div class="prose max-w-none">
                    <ul>
                        @foreach($comparisonData['changelog']['changes'] as $type => $changes)
                            @foreach($changes as $change)
                                <li>
                                    <span class="font-medium">{{ ucfirst($type) }}:</span> 
                                    {{ $change['file'] }} - {{ $change['description'] }}
                                </li>
                            @endforeach
                        @endforeach
                    </ul>
                </div>

                @if($showRollbackHistory && count($rollbackHistory) > 0)
                    <div class="mt-6">
                        <h4 class="font-medium mb-2">Rollback History</h4>
                        <div class="space-y-4">
                            @foreach($rollbackHistory as $rollback)
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between">
                                        <span class="font-medium">
                                            Rollback from v{{ $rollback->from_version->getSemanticVersion() }} 
                                            to v{{ $rollback->to_version->getSemanticVersion() }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ $rollback->created_at->format('M d, Y H:i') }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        Initiated by: {{ $rollback->user->name }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            @if(!empty($comparisonData))
                <div class="px-6 py-4 border-t border-gray-200">
                    <livewire:theme-rollback-confirm 
                        :theme="$theme"
                        :from-version="$version1"
                        :to-version="$version2"
                        key="rollback-confirm-{{ $version1 }}-{{ $version2 }}"
                    />
                </div>
            @endif
        </div>
    @endif
</div>
