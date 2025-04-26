<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-2">{{ $title }}</h3>
    
    <div class="flex items-center justify-between">
        <span class="text-3xl font-bold {{ $getColorClass() }}">
            @if($type === 'score')
                {{ number_format($value, 1) }}
            @elseif($type === 'security')
                {{ $value }} issues
            @elseif($type === 'performance')
                {{ number_format($value, 2) }}ms
            @else
                {{ number_format($value, 2) }}
            @endif
        </span>

        @if($change !== null)
            <span class="flex items-center {{ $getChangeColor() }}">
                {{ $getChangeIcon() }} {{ number_format(abs($change), 2) }}
            </span>
        @endif
    </div>

    @if($type === 'score')
        <div class="mt-4 w-full bg-gray-200 rounded-full h-2.5">
            <div class="bg-green-600 h-2.5 rounded-full" 
                 style="width: {{ $value }}%"></div>
        </div>
        <div class="mt-1 text-xs text-gray-500">
            @if($value >= 80)
                Excellent quality
            @elseif($value >= 60)
                Good quality
            @else
                Needs improvement
            @endif
        </div>
    @endif

    @if($type === 'performance' && !empty($details))
        <div class="mt-3 border-t pt-3">
            <h4 class="text-sm font-medium mb-1">Performance Breakdown:</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div>File Operations: <span class="font-medium {{ $details['file_operations'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $details['file_operations'] >= 0 ? '+' : '' }}{{ number_format($details['file_operations'], 2) }}ms
                </span></div>
                <div>Line Changes: <span class="font-medium {{ $details['line_changes'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $details['line_changes'] >= 0 ? '+' : '' }}{{ number_format($details['line_changes'], 2) }}ms
                </span></div>
                <div>Size Impact: <span class="font-medium {{ $details['size_impact'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $details['size_impact'] >= 0 ? '+' : '' }}{{ number_format($details['size_impact'], 2) }}ms
                </span></div>
                <div>File Type Impact: <span class="font-medium {{ $details['file_type_impact'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $details['file_type_impact'] >= 0 ? '+' : '' }}{{ number_format($details['file_type_impact'], 2) }}ms
                </span></div>
            </div>
            <div class="mt-2 text-sm {{ $getColorClass() }}">
                @if($value > 0)
                    Potential performance regression (+{{ $value }}ms)
                @elseif($value < 0)
                    Potential performance improvement ({{ $value }}ms)
                @else
                    Neutral performance impact
                @endif
            </div>
        </div>
    @endif

    @if($type === 'security')
        <div class="mt-2 text-sm {{ $getColorClass() }}">
            @if($value == 0)
                No security issues found
            @else
                {{ $value }} security issue(s) detected
            @endif
        </div>
        @if(!empty($details))
            <div class="mt-3 border-t pt-3">
                <h4 class="text-sm font-medium mb-1">Issue Details:</h4>
                <ul class="text-xs space-y-1">
                    @foreach($details as $issue)
                        <li class="flex items-start">
                            <span class="inline-block w-2 h-2 rounded-full bg-red-500 mt-1 mr-2"></span>
                            <span>{{ $issue['file'] }}:{{ $issue['line'] }} - {{ $issue['message'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endif

    @if($type === 'complexity' && !empty($details))
        <div class="mt-3 border-t pt-3">
            <h4 class="text-sm font-medium mb-1">Complexity Breakdown:</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div>Cyclomatic: <span class="font-medium">{{ $details['cyclomatic'] ?? 'N/A' }}</span></div>
                <div>Cognitive: <span class="font-medium">{{ $details['cognitive'] ?? 'N/A' }}</span></div>
                <div>Maintainability: <span class="font-medium">{{ $details['maintainability'] ?? 'N/A' }}</span></div>
                <div>Halstead: <span class="font-medium">{{ $details['halstead'] ?? 'N/A' }}</span></div>
            </div>
        </div>
    @endif

    @if($type === 'size_metrics' && !empty($details))
        <div class="mt-3 border-t pt-3">
            <h4 class="text-sm font-medium mb-1">Size Breakdown:</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div>CSS Change: <span class="font-medium {{ $details['css_size_change'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $details['css_size_change'] >= 0 ? '+' : '' }}{{ number_format($details['css_size_change'] / 1024, 2) }} KB
                </span></div>
                <div>JS Change: <span class="font-medium {{ $details['js_size_change'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $details['js_size_change'] >= 0 ? '+' : '' }}{{ number_format($details['js_size_change'] / 1024, 2) }} KB
                </span></div>
                <div>Images Change: <span class="font-medium {{ $details['image_size_change'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $details['image_size_change'] >= 0 ? '+' : '' }}{{ number_format($details['image_size_change'] / 1024, 2) }} KB
                </span></div>
                <div>Total Compression: <span class="font-medium">{{ $details['compression_ratio'] ?? 'N/A' }}%</span></div>
                <div>CSS Compression: <span class="font-medium">{{ $details['css_compression_ratio'] ?? 'N/A' }}%</span></div>
                <div>JS Compression: <span class="font-medium">{{ $details['js_compression_ratio'] ?? 'N/A' }}%</span></div>
                <div>Image Compression: <span class="font-medium">{{ $details['image_compression_ratio'] ?? 'N/A' }}%</span></div>
            </div>
            @if(!empty($details['file_size_distribution']))
                <div class="mt-2">
                    <h5 class="text-xs font-medium">File Size Distribution:</h5>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>Before: 
                            <span class="font-medium">0-10KB: {{ $details['file_size_distribution']['before']['0-10KB'] ?? 0 }}</span>,
                            <span class="font-medium">10-100KB: {{ $details['file_size_distribution']['before']['10-100KB'] ?? 0 }}</span>,
                            <span class="font-medium">100KB-1MB: {{ $details['file_size_distribution']['before']['100KB-1MB'] ?? 0 }}</span>,
                            <span class="font-medium">1MB+: {{ $details['file_size_distribution']['before']['1MB+'] ?? 0 }}</span>
                        </div>
                        <div>After: 
                            <span class="font-medium">0-10KB: {{ $details['file_size_distribution']['after']['0-10KB'] ?? 0 }}</span>,
                            <span class="font-medium">10-100KB: {{ $details['file_size_distribution']['after']['10-100KB'] ?? 0 }}</span>,
                            <span class="font-medium">100KB-1MB: {{ $details['file_size_distribution']['after']['100KB-1MB'] ?? 0 }}</span>,
                            <span class="font-medium">1MB+: {{ $details['file_size_distribution']['after']['1MB+'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            @endif

            @if(!empty($details['largest_files']))
                <div class="mt-2">
                    <h5 class="text-xs font-medium">Largest Files:</h5>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div>Before: <span class="font-medium">{{ $details['largest_files']['before']['name'] }}</span> ({{ number_format($details['largest_files']['before']['size'] / 1024, 2) }} KB)</div>
                        <div>After: <span class="font-medium">{{ $details['largest_files']['after']['name'] }}</span> ({{ number_format($details['largest_files']['after']['size'] / 1024, 2) }} KB)</div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($type === 'compatibility' && !empty($details))
        <div class="mt-3 border-t pt-3">
            <h4 class="text-sm font-medium mb-1">Compatibility Checks:</h4>
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex items-center">
                    @if($details['cms_version'])
                        <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    @endif
                    <span>CMS Version</span>
                </div>
                <div class="flex items-center">
                    @if($details['required_dependencies'])
                        <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    @endif
                    <span>Required Dependencies</span>
                </div>
                <div class="flex items-center">
                    @if($details['conflicts'])
                        <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    @else
                        <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    @endif
                    <span>No Conflicts</span>
                </div>
                <div class="flex items-center">
                    @if($details['overall'])
                        <span class="font-medium text-green-600">Fully Compatible</span>
                    @else
                        <span class="font-medium text-red-600">Compatibility Issues</span>
                    @endif
                </div>
            </div>
        </div>
    @endif

    @if($type === 'dependency_changes' && !empty($details))
        <div class="mt-3 border-t pt-3">
            <h4 class="text-sm font-medium mb-1">Dependency Changes:</h4>
            <div class="space-y-3">
                <div>
                    <h5 class="text-xs font-medium">Required Dependencies:</h5>
                    @if(!empty($details['required_dependencies']['added']))
                        <div class="text-xs mt-1">
                            <span class="font-medium text-green-600">Added:</span>
                            <ul class="ml-4 list-disc">
                                @foreach($details['required_dependencies']['added'] as $dep)
                                    <li>{{ $dep }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(!empty($details['required_dependencies']['removed']))
                        <div class="text-xs mt-1">
                            <span class="font-medium text-red-600">Removed:</span>
                            <ul class="ml-4 list-disc">
                                @foreach($details['required_dependencies']['removed'] as $dep)
                                    <li>{{ $dep }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(!empty($details['required_dependencies']['changed']))
                        <div class="text-xs mt-1">
                            <span class="font-medium text-yellow-600">Changed:</span>
                            <ul class="ml-4 list-disc">
                                @foreach($details['required_dependencies']['changed'] as $dep => $versions)
                                    <li>{{ $dep }}: {{ $versions['from'] }} → {{ $versions['to'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <div>
                    <h5 class="text-xs font-medium">Optional Dependencies:</h5>
                    @if(!empty($details['optional_dependencies']['added']))
                        <div class="text-xs mt-1">
                            <span class="font-medium text-green-600">Added:</span>
                            <ul class="ml-4 list-disc">
                                @foreach($details['optional_dependencies']['added'] as $dep)
                                    <li>{{ $dep }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(!empty($details['optional_dependencies']['removed']))
                        <div class="text-xs mt-1">
                            <span class="font-medium text-red-600">Removed:</span>
                            <ul class="ml-4 list-disc">
                                @foreach($details['optional_dependencies']['removed'] as $dep)
                                    <li>{{ $dep }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(!empty($details['optional_dependencies']['changed']))
                        <div class="text-xs mt-1">
                            <span class="font-medium text-yellow-600">Changed:</span>
                            <ul class="ml-4 list-disc">
                                @foreach($details['optional_dependencies']['changed'] as $dep => $versions)
                                    <li>{{ $dep }}: {{ $versions['from'] }} → {{ $versions['to'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                @if(!empty($details['conflicts']))
                <div>
                    <h5 class="text-xs font-medium">Conflicts:</h5>
                    @if(!empty($details['conflicts']['added']))
                        <div class="text-xs mt-1">
                            <span class="font-medium text-red-600">Added:</span>
                            <ul class="ml-4 list-disc">
                                @foreach($details['conflicts']['added'] as $dep)
                                    <li>{{ $dep }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if(!empty($details['conflicts']['removed']))
                        <div class="text-xs mt-1">
                            <span class="font-medium text-green-600">Removed:</span>
                            <ul class="ml-4 list-disc">
                                @foreach($details['conflicts']['removed'] as $dep)
                                    <li>{{ $dep }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
                @endif

                @if(!empty($details['cms_version_changes']))
                <div>
                    <h5 class="text-xs font-medium">CMS Version Changes:</h5>
                    <div class="text-xs mt-1">
                        @if($details['cms_version_changes']['min_version'])
                            <div class="font-medium">Minimum CMS version changed</div>
                        @endif
                        @if($details['cms_version_changes']['max_version'])
                            <div class="font-medium">Maximum CMS version changed</div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    @endif
</div>
