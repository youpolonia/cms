@php
use App\Models\ThemeVersionComparisonStat;
@endphp

@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">
                Comparison Statistics: {{ $version->name }} vs {{ $comparedVersion->name }}
            </h1>
            <a href="{{ route('themes.versions.compare', [$version->theme_id, $version->id, $comparedVersion->id]) }}" 
               class="btn btn-secondary">
                Back to Diff View
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-xl font-semibold mb-4">File Changes</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span>Files Added:</span>
                            <span class="font-medium text-green-600">{{ $stats->files_added }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Files Removed:</span>
                            <span class="font-medium text-red-600">{{ $stats->files_removed }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Files Modified:</span>
                            <span class="font-medium text-blue-600">{{ $stats->files_modified }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold mb-4">Code Changes</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span>Lines Added:</span>
                            <span class="font-medium text-green-600">{{ $stats->lines_added }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Lines Removed:</span>
                            <span class="font-medium text-red-600">{{ $stats->lines_removed }}</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold mb-4">Size Changes</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span>Total Size Before:</span>
                            <span class="font-medium">{{ app('App\Services\VersionComparisonService')->formatBytes($stats->total_size_before) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Size After:</span>
                            <span class="font-medium">{{ app('App\Services\VersionComparisonService')->formatBytes($stats->total_size_after) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Size Change:</span>
                            <span class="font-medium {{ $stats->size_change >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $stats->size_change >= 0 ? '+' : '' }}{{ app('App\Services\VersionComparisonService')->formatBytes($stats->size_change) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>CSS Change:</span>
                            <span class="font-medium {{ $stats->css_size_change >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $stats->css_size_change >= 0 ? '+' : '' }}{{ app('App\Services\VersionComparisonService')->formatBytes($stats->css_size_change) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>JS Change:</span>
                            <span class="font-medium {{ $stats->js_size_change >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $stats->js_size_change >= 0 ? '+' : '' }}{{ app('App\Services\VersionComparisonService')->formatBytes($stats->js_size_change) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Images Change:</span>
                            <span class="font-medium {{ $stats->image_size_change >= 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $stats->image_size_change >= 0 ? '+' : '' }}{{ app('App\Services\VersionComparisonService')->formatBytes($stats->image_size_change) }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span>Total Compression:</span>
                            <span class="font-medium">{{ $stats->compression_ratio }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>CSS Compression:</span>
                            <span class="font-medium">{{ $stats->css_compression_ratio }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>JS Compression:</span>
                            <span class="font-medium">{{ $stats->js_compression_ratio }}%</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Image Compression:</span>
                            <span class="font-medium">{{ $stats->image_compression_ratio }}%</span>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-xl font-semibold mb-4">Dependency Changes</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span>Overall Status:</span>
                            <span class="font-medium capitalize">{{ $stats->comparison_data['dependency_changes']['status'] ?? 'none' }}</span>
                        </div>
                        
                        <div class="border-t pt-4">
                            <h3 class="font-medium mb-2">Required Dependencies</h3>
                            @if(!empty($stats->comparison_data['dependency_changes']['required_dependencies']['added']))
                                <div class="text-sm text-green-600 mb-1">
                                    <span class="font-medium">Added:</span>
                                    {{ implode(', ', $stats->comparison_data['dependency_changes']['required_dependencies']['added']) }}
                                </div>
                            @endif
                            @if(!empty($stats->comparison_data['dependency_changes']['required_dependencies']['removed']))
                                <div class="text-sm text-red-600 mb-1">
                                    <span class="font-medium">Removed:</span>
                                    {{ implode(', ', $stats->comparison_data['dependency_changes']['required_dependencies']['removed']) }}
                                </div>
                            @endif
                            @if(!empty($stats->comparison_data['dependency_changes']['required_dependencies']['changed']))
                                <div class="text-sm text-blue-600">
                                    <span class="font-medium">Changed:</span>
                                    @foreach($stats->comparison_data['dependency_changes']['required_dependencies']['changed'] as $dep => $change)
                                        <div class="ml-2">
                                            {{ $dep }}: {{ $change['from'] }} → {{ $change['to'] }}
                                            <span class="text-xs">({{ $change['change_type'] }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="font-medium mb-2">Optional Dependencies</h3>
                            @if(!empty($stats->comparison_data['dependency_changes']['optional_dependencies']['added']))
                                <div class="text-sm text-green-600 mb-1">
                                    <span class="font-medium">Added:</span>
                                    {{ implode(', ', $stats->comparison_data['dependency_changes']['optional_dependencies']['added']) }}
                                </div>
                            @endif
                            @if(!empty($stats->comparison_data['dependency_changes']['optional_dependencies']['removed']))
                                <div class="text-sm text-red-600 mb-1">
                                    <span class="font-medium">Removed:</span>
                                    {{ implode(', ', $stats->comparison_data['dependency_changes']['optional_dependencies']['removed']) }}
                                </div>
                            @endif
                            @if(!empty($stats->comparison_data['dependency_changes']['optional_dependencies']['changed']))
                                <div class="text-sm text-blue-600">
                                    <span class="font-medium">Changed:</span>
                                    @foreach($stats->comparison_data['dependency_changes']['optional_dependencies']['changed'] as $dep => $change)
                                        <div class="ml-2">
                                            {{ $dep }}: {{ $change['from'] }} → {{ $change['to'] }}
                                            <span class="text-xs">({{ $change['change_type'] }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <div class="border-t pt-4">
                            <h3 class="font-medium mb-2">Conflicts</h3>
                            @if(!empty($stats->comparison_data['dependency_changes']['conflicts']['added']))
                                <div class="text-sm text-red-600 mb-1">
                                    <span class="font-medium">Added:</span>
                                    {{ implode(', ', $stats->comparison_data['dependency_changes']['conflicts']['added']) }}
                                </div>
                            @endif
                            @if(!empty($stats->comparison_data['dependency_changes']['conflicts']['removed']))
                                <div class="text-sm text-green-600 mb-1">
                                    <span class="font-medium">Removed:</span>
                                    {{ implode(', ', $stats->comparison_data['dependency_changes']['conflicts']['removed']) }}
                                </div>
                            @endif
                            @if(!empty($stats->comparison_data['dependency_changes']['conflicts']['changed']))
                                <div class="text-sm text-blue-600">
                                    <span class="font-medium">Changed:</span>
                                    @foreach($stats->comparison_data['dependency_changes']['conflicts']['changed'] as $dep => $change)
                                        <div class="ml-2">
                                            {{ $dep }}: {{ $change['from'] }} → {{ $change['to'] }}
                                            <span class="text-xs">({{ $change['change_type'] }})</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($stats->file_size_distribution))
            <div class="mt-6">
                <h3 class="text-lg font-semibold mb-2">File Size Distribution</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium mb-1">Before</h4>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span>0-10KB:</span>
                                <span class="font-medium">{{ $stats->file_size_distribution['before']['0-10KB'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>10-100KB:</span>
                                <span class="font-medium">{{ $stats->file_size_distribution['before']['10-100KB'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>100KB-1MB:</span>
                                <span class="font-medium">{{ $stats->file_size_distribution['before']['100KB-1MB'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>1MB+:</span>
                                <span class="font-medium">{{ $stats->file_size_distribution['before']['1MB+'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium mb-1">After</h4>
                        <div class="space-y-1 text-sm">
                            <div class="flex justify-between">
                                <span>0-10KB:</span>
                                <span class="font-medium">{{ $stats->file_size_distribution['after']['0-10KB'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>10-100KB:</span>
                                <span class="font-medium">{{ $stats->file_size_distribution['after']['10-100KB'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>100KB-1MB:</span>
                                <span class="font-medium">{{ $stats->file_size_distribution['after']['100KB-1MB'] ?? 0 }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>1MB+:</span>
                                <span class="font-medium">{{ $stats->file_size_distribution['after']['1MB+'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(!empty($stats->largest_files))
            <div class="mt-4">
                <h3 class="text-lg font-semibold mb-2">Largest Files</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium mb-1">Before</h4>
                        <div class="text-sm">
                            <div class="font-medium">{{ $stats->largest_files['before']['name'] ?? 'N/A' }}</div>
                            <div>{{ app('App\Services\VersionComparisonService')->formatBytes($stats->largest_files['before']['size'] ?? 0) }}</div>
                        </div>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium mb-1">After</h4>
                        <div class="text-sm">
                            <div class="font-medium">{{ $stats->largest_files['after']['name'] ?? 'N/A' }}</div>
                            <div>{{ app('App\Services\VersionComparisonService')->formatBytes($stats->largest_files['after']['size'] ?? 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
            <x-theme-comparison-stats-widget 
                title="Quality Score" 
                :value="$stats->quality_score"
                type="score"
                :change="$stats->quality_score - ($previousComparison->quality_score ?? $stats->quality_score)"
            />

            <x-theme-comparison-stats-widget 
                title="Complexity Change" 
                :value="$stats->complexity_change"
                type="complexity"
                :change="$stats->complexity_change - ($previousComparison->complexity_change ?? 0)"
                :details="$stats->comparison_data['quality_metrics']['complexity'] ?? null"
            />

            <x-theme-comparison-stats-widget 
                title="Coverage Change" 
                :value="$stats->coverage_change"
                type="score"
                :change="$stats->coverage_change - ($previousComparison->coverage_change ?? 0)"
            />

            <x-theme-comparison-stats-widget 
                title="Security Issues" 
                :value="$stats->security_issues"
                type="security"
                :details="$stats->comparison_data['security_issues'] ?? null"
            />

            <x-theme-comparison-stats-widget 
                title="Performance Impact" 
                :value="$stats->performance_impact"
                type="performance"
                :change="$stats->performance_impact - ($previousComparison->performance_impact ?? 0)"
            />
        </div>

        @if(!empty($stats->comparison_data))
            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-4">Detailed Metrics</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">File Type Breakdown</h3>
                        <div class="space-y-2">
                            @foreach($stats->comparison_data['diff_stats']['file_types'] ?? [] as $type => $count)
                                <div class="flex justify-between">
                                    <span class="capitalize">{{ $type ?: 'Unknown' }} files:</span>
                                    <span class="font-medium">{{ $count }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Security Issues</h3>
                        @if(!empty($stats->comparison_data['security_issues']))
                            <div class="space-y-3">
                                @foreach($stats->comparison_data['security_issues'] as $issue)
                                    <div class="text-sm">
                                        <div class="font-medium">{{ $issue['file'] }}:{{ $issue['line'] }}</div>
                                        <div class="text-gray-600">{{ $issue['message'] }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500">No security issues found</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold mb-4">Raw Comparison Data</h3>
                    <pre class="text-xs overflow-x-auto">{{ json_encode($stats->comparison_data, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        @endif
    </div>
@endsection
