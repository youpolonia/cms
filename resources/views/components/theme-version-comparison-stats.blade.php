@props(['comparison'])

<div class="bg-white rounded-lg shadow-md p-6 space-y-6">
    <h2 class="text-xl font-semibold text-gray-800">Version Comparison Metrics</h2>

    <!-- File Changes Section -->
    <div class="border rounded-lg p-4">
        <h3 class="text-lg font-medium mb-4">File Changes</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-blue-800">Files Added</div>
                <div class="text-2xl font-bold text-blue-600">{{ $comparison->files_added }}</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-red-800">Files Removed</div>
                <div class="text-2xl font-bold text-red-600">{{ $comparison->files_removed }}</div>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-yellow-800">Files Modified</div>
                <div class="text-2xl font-bold text-yellow-600">{{ $comparison->files_modified }}</div>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-800">Lines Added</div>
                <div class="text-xl font-bold text-gray-700">{{ $comparison->lines_added }}</div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-gray-800">Lines Removed</div>
                <div class="text-xl font-bold text-gray-700">{{ $comparison->lines_removed }}</div>
            </div>
        </div>
    </div>

    <!-- Size Metrics Section -->
    <div class="border rounded-lg p-4">
        <h3 class="text-lg font-medium mb-4">Size Metrics</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-purple-800">Total Size Change</div>
                <div class="text-xl font-bold {{ $comparison->size_change >= 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $comparison->size_change >= 0 ? '+' : '' }}{{ number_format($comparison->size_change / 1024, 2) }} KB
                    <span class="text-sm">({{ $comparison->size_change_percent >= 0 ? '+' : '' }}{{ $comparison->size_change_percent }}%)</span>
                </div>
                <div class="text-xs mt-1">
                    Before: {{ number_format($comparison->total_size_before / 1024, 2) }} KB
                    → After: {{ number_format($comparison->total_size_after / 1024, 2) }} KB
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div class="bg-indigo-50 p-3 rounded-lg">
                    <div class="text-xs font-medium text-indigo-800">CSS</div>
                    <div class="text-sm {{ ($comparison->css_size_after - $comparison->css_size_before) >= 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ ($comparison->css_size_after - $comparison->css_size_before) >= 0 ? '+' : '' }}{{ number_format(($comparison->css_size_after - $comparison->css_size_before) / 1024, 2) }} KB
                    </div>
                </div>
                <div class="bg-blue-50 p-3 rounded-lg">
                    <div class="text-xs font-medium text-blue-800">JS</div>
                    <div class="text-sm {{ ($comparison->js_size_after - $comparison->js_size_before) >= 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ ($comparison->js_size_after - $comparison->js_size_before) >= 0 ? '+' : '' }}{{ number_format(($comparison->js_size_after - $comparison->js_size_before) / 1024, 2) }} KB
                    </div>
                </div>
                <div class="bg-green-50 p-3 rounded-lg">
                    <div class="text-xs font-medium text-green-800">Images</div>
                    <div class="text-sm {{ ($comparison->image_size_after - $comparison->image_size_before) >= 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ ($comparison->image_size_after - $comparison->image_size_before) >= 0 ? '+' : '' }}{{ number_format(($comparison->image_size_after - $comparison->image_size_before) / 1024, 2) }} KB
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Metrics Section -->
    <div class="border rounded-lg p-4">
        <h3 class="text-lg font-medium mb-4">Quality Metrics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-green-800">Quality Score</div>
                <div class="text-2xl font-bold text-green-600">{{ number_format($comparison->quality_score, 1) }}/100</div>
                <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-green-600 h-2 rounded-full" style="width: {{ $comparison->quality_score }}%"></div>
                </div>
                <div class="text-xs mt-1">
                    @if($comparison->quality_score >= 80)
                        Excellent quality
                    @elseif($comparison->quality_score >= 60)
                        Good quality
                    @else
                        Needs improvement
                    @endif
                </div>
            </div>
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-blue-800">Complexity Change</div>
                <div class="text-2xl font-bold {{ $comparison->complexity_change >= 0 ? 'text-red-600' : 'text-green-600' }}">
                    {{ $comparison->complexity_change >= 0 ? '+' : '' }}{{ number_format($comparison->complexity_change, 1) }}
                </div>
                <div class="text-xs mt-1">
                    @if($comparison->complexity_change > 0)
                        More complex
                    @elseif($comparison->complexity_change < 0)
                        Less complex
                    @else
                        No change
                    @endif
                </div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm font-medium text-purple-800">Coverage Change</div>
                <div class="text-2xl font-bold {{ $comparison->coverage_change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $comparison->coverage_change >= 0 ? '+' : '' }}{{ number_format($comparison->coverage_change, 1) }}%
                </div>
                <div class="text-xs mt-1">
                    @if($comparison->coverage_change > 0)
                        Improved coverage
                    @elseif($comparison->coverage_change < 0)
                        Reduced coverage
                    @else
                        No change
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Dependencies Section -->
    @if(!empty($comparison->comparison_data['dependency_changes']))
    <div class="border rounded-lg p-4">
        <h3 class="text-lg font-medium mb-4">Dependency Changes</h3>
        <div class="space-y-4">
            @if(!empty($comparison->comparison_data['dependency_changes']['required_dependencies']['added']))
            <div>
                <h4 class="text-sm font-medium text-green-700">Added Required Dependencies</h4>
                <ul class="text-sm ml-4 list-disc">
                    @foreach($comparison->comparison_data['dependency_changes']['required_dependencies']['added'] as $dep)
                        <li>{{ $dep }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($comparison->comparison_data['dependency_changes']['required_dependencies']['removed']))
            <div>
                <h4 class="text-sm font-medium text-red-700">Removed Required Dependencies</h4>
                <ul class="text-sm ml-4 list-disc">
                    @foreach($comparison->comparison_data['dependency_changes']['required_dependencies']['removed'] as $dep)
                        <li>{{ $dep }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($comparison->comparison_data['dependency_changes']['required_dependencies']['changed']))
            <div>
                <h4 class="text-sm font-medium text-yellow-700">Changed Required Dependencies</h4>
                <ul class="text-sm ml-4 list-disc">
                    @foreach($comparison->comparison_data['dependency_changes']['required_dependencies']['changed'] as $dep => $versions)
                        <li>{{ $dep }}: {{ $versions['from'] }} → {{ $versions['to'] }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!empty($comparison->comparison_data['dependency_changes']['cms_version_changes']))
            <div>
                <h4 class="text-sm font-medium">CMS Version Requirements</h4>
                <div class="text-sm">
                    @if($comparison->comparison_data['dependency_changes']['cms_version_changes']['min_version'])
                        <div>Minimum CMS version changed</div>
                    @endif
                    @if($comparison->comparison_data['dependency_changes']['cms_version_changes']['max_version'])
                        <div>Maximum CMS version changed</div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Performance Impact -->
    <div class="border rounded-lg p-4">
        <h3 class="text-lg font-medium mb-4">Performance Impact</h3>
        <div class="text-2xl font-bold {{ $comparison->performance_impact >= 0 ? 'text-red-600' : 'text-green-600' }}">
            {{ $comparison->performance_impact >= 0 ? '+' : '' }}{{ number_format($comparison->performance_impact, 2) }}
        </div>
        <div class="text-sm mt-1">
            @if($comparison->performance_impact > 0)
                Potential performance regression
            @elseif($comparison->performance_impact < 0)
                Potential performance improvement
            @else
                Neutral performance impact
            @endif
        </div>
    </div>
</div>
