@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Compare Theme Versions</h1>
        <a href="{{ route('themes.show', $theme->id) }}" 
           class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded">
            Back to Theme
        </a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">{{ $theme->name }}</h2>
            <div class="flex space-x-4">
                <div class="relative">
                    <select id="version1" class="appearance-none bg-white border border-gray-300 rounded-md px-4 py-2 pr-8">
                        @foreach($versions as $version)
                            <option value="{{ $version->id }}" {{ $version->id == $version1->id ? 'selected' : '' }}>
                                Version {{ $version->version }} ({{ $version->created_at->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <select id="version2" class="appearance-none bg-white border border-gray-300 rounded-md px-4 py-2 pr-8">
                        @foreach($versions as $version)
                            <option value="{{ $version->id }}" {{ $version->id == $version2->id ? 'selected' : '' }}>
                                Version {{ $version->version }} ({{ $version->created_at->format('M d, Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button onclick="compareVersions()" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Compare
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Version {{ $version1->version }}</h3>
                    <div x-data="{ showRollbackConfirm: false }" class="relative">
                        <button @click="showRollbackConfirm = true" 
                                class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                            Rollback
                        </button>
                        <div x-show="showRollbackConfirm" 
                             x-transition
                             class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10 p-4 border border-gray-200">
                            <p class="text-sm mb-3">Rollback to this version?</p>
                            <div class="flex justify-end space-x-2">
                                <button @click="showRollbackConfirm = false" 
                                        class="text-gray-500 text-sm">
                                    Cancel
                                </button>
                                <form method="POST" action="{{ route('themes.versions.rollback', [$theme, $version1]) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                                        Confirm
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded">
                    <pre class="text-sm">{{ json_encode(json_decode($version1->config), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium">Version {{ $version2->version }}</h3>
                    <div x-data="{ showRestoreConfirm: false }" class="relative">
                        <button @click="showRestoreConfirm = true" 
                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            Restore
                        </button>
                        <div x-show="showRestoreConfirm" 
                             x-transition
                             class="absolute right-0 mt-2 w-64 bg-white rounded-md shadow-lg z-10 p-4 border border-gray-200">
                            <p class="text-sm mb-3">Restore this version as new?</p>
                            <div class="flex justify-end space-x-2">
                                <button @click="showRestoreConfirm = false" 
                                        class="text-gray-500 text-sm">
                                    Cancel
                                </button>
                                <form method="POST" action="{{ route('themes.versions.restore', [$theme, $version2]) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                        Confirm
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 p-4 rounded">
                    <pre class="text-sm">{{ json_encode(json_decode($version2->config), JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <button @click="activeTab = 'summary'" 
                            :class="activeTab === 'summary' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Summary
                    </button>
                    <button @click="activeTab = 'files'" 
                            :class="activeTab === 'files' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        File Changes
                    </button>
                <button @click="activeTab = 'config'" 
                        :class="activeTab === 'config' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Config Changes
                </button>
                <button @click="activeTab = 'metrics'" 
                        :class="activeTab === 'metrics' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Metrics
                </button>
                </nav>
            </div>

            <div x-show="activeTab === 'summary'" class="mt-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500">Version Change</h4>
                        <p class="mt-1 text-lg font-semibold capitalize">{{ $diff['version_info']['version_change'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500">Added Files</h4>
                        <p class="mt-1 text-lg font-semibold">{{ $diff['summary']['added_files'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500">Deleted Files</h4>
                        <p class="mt-1 text-lg font-semibold">{{ $diff['summary']['deleted_files'] }}</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h4 class="text-sm font-medium text-gray-500">Modified Files</h4>
                        <p class="mt-1 text-lg font-semibold">{{ $diff['summary']['modified_files'] }}</p>
                    </div>
                </div>
            </div>

            <div x-show="activeTab === 'files'" class="mt-4">
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <ul class="divide-y divide-gray-200">
                        @foreach($diff['files']['added'] as $file => $details)
                        <li class="px-4 py-4 sm:px-6 bg-green-50">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-green-800 truncate">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mr-2">
                                        Added
                                    </span>
                                    {{ $file }}
                                </p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="text-sm text-gray-500">{{ $details['size'] }} bytes</p>
                                </div>
                            </div>
                        </li>
                        @endforeach

                        @foreach($diff['files']['deleted'] as $file => $details)
                        <li class="px-4 py-4 sm:px-6 bg-red-50">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-red-800 truncate">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mr-2">
                                        Removed
                                    </span>
                                    {{ $file }}
                                </p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="text-sm text-gray-500">{{ $details['size'] }} bytes</p>
                                </div>
                            </div>
                        </li>
                        @endforeach

                        @foreach($diff['files']['modified'] as $file => $details)
                        <li class="px-4 py-4 sm:px-6 bg-blue-50">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-blue-800 truncate">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mr-2">
                                        Modified
                                    </span>
                                    {{ $file }}
                                </p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="text-sm text-gray-500">{{ $details['old_size'] }} → {{ $details['new_size'] }} bytes</p>
                                </div>
                            </div>
                            <div class="mt-2">
                                @livewire('file-diff-viewer', [
                                    'filePath' => $file,
                                    'oldContent' => $details['old_content'] ?? '',
                                    'newContent' => $details['new_content'] ?? '',
                                    'oldVersion' => $version1->version,
                                    'newVersion' => $version2->version
                                ])
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div x-show="activeTab === 'config'" class="mt-4">
                <!-- Config changes content remains the same -->
            </div>

            <div x-show="activeTab === 'metrics'" class="mt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Performance Metrics</h3>
                        <canvas id="performanceChart" class="w-full h-64"></canvas>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">File Size Changes</h3>
                        <canvas id="sizeChart" class="w-full h-64"></canvas>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Code Quality</h3>
                        <canvas id="qualityChart" class="w-full h-64"></canvas>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold mb-4">Dependencies</h3>
                        <canvas id="dependencyChart" class="w-full h-64"></canvas>
                    </div>
                </div>
            </div>
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <ul class="divide-y divide-gray-200">
                        @foreach($diff['manifests'] as $key => $change)
                        <li class="px-4 py-4 sm:px-6">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $key }}</p>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <span @class([
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        'bg-green-100 text-green-800' => $change['status'] === 'added',
                                        'bg-red-100 text-red-800' => $change['status'] === 'removed',
                                        'bg-blue-100 text-blue-800' => $change['status'] === 'changed'
                                    ])>
                                        {{ ucfirst($change['status']) }}
                                    </span>
                                </div>
                            </div>
                            @if($change['status'] === 'changed')
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="text-xs text-gray-500 mb-1">Old Value</p>
                                    <pre class="text-sm">{{ json_encode($change['old'], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                                <div class="bg-gray-50 p-3 rounded">
                                    <p class="text-xs text-gray-500 mb-1">New Value</p>
                                    <pre class="text-sm">{{ json_encode($change['new'], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            </div>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            function initCharts() {
                // Performance Chart
                new Chart(document.getElementById('performanceChart'), {
                    type: 'bar',
                    data: {
                        labels: ['Load Time (ms)', 'Memory Usage (MB)'],
                        datasets: [{
                            label: 'Version {{ $version1->version }}',
                            data: [{{ $stats->load_time_ms }}, {{ $stats->memory_usage_mb }}],
                            backgroundColor: 'rgba(59, 130, 246, 0.5)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Version {{ $version2->version }}',
                            data: [{{ $stats->load_time_diff_ms + $stats->load_time_ms }}, {{ $stats->memory_usage_diff_mb + $stats->memory_usage_mb }}],
                            backgroundColor: 'rgba(16, 185, 129, 0.5)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });

                // Size Chart
                new Chart(document.getElementById('sizeChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['CSS', 'JS', 'Images'],
                        datasets: [{
                            data: [{{ $stats->css_size_diff_kb }}, {{ $stats->js_size_diff_kb }}, {{ $stats->image_size_diff_kb }}],
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.5)',
                                'rgba(54, 162, 235, 0.5)',
                                'rgba(255, 206, 86, 0.5)',
                                'rgba(75, 192, 192, 0.5)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)'
                            ],
                            borderWidth: 1
                        }]
                    }
                });

                // Quality Chart
                new Chart(document.getElementById('qualityChart'), {
                    type: 'radar',
                    data: {
                        labels: ['Quality Score', 'Performance Impact', 'Complexity'],
                        datasets: [{
                            label: 'Quality Metrics',
                            data: [{{ $stats->quality_score }}, {{ $stats->performance_impact }}, {{ $stats->complexity }}],
                            backgroundColor: 'rgba(75, 192, 192, 0.2)',
                            borderColor: 'rgba(75, 192, 192, 1)',
                            pointBackgroundColor: 'rgba(75, 192, 192, 1)',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: 'rgba(75, 192, 192, 1)'
                        }]
                    }
                });

                // Dependency Chart
                new Chart(document.getElementById('dependencyChart'), {
                    type: 'pie',
                    data: {
                        labels: ['Added', 'Removed', 'Updated', 'Same'],
                        datasets: [{
                            data: [{{ count($diff['dependencies']['added']) }}, {{ count($diff['dependencies']['removed']) }}, {{ count($diff['dependencies']['updated']) }}, {{ count($diff['dependencies']['same']) }}],
                            backgroundColor: [
                                'rgba(75, 192, 192, 0.5)',
                                'rgba(255, 99, 132, 0.5)',
                                'rgba(255, 206, 86, 0.5)',
                                'rgba(153, 102, 255, 0.5)'
                            ],
                            borderColor: [
                                'rgba(75, 192, 192, 1)',
                                'rgba(255, 99, 132, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(153, 102, 255, 1)'
                            ],
                            borderWidth: 1
                        }]
                    }
                });
            }

            document.addEventListener('alpine:init', () => {
                Alpine.data('versionComparison', () => ({
                    activeTab: 'summary',
                    init() {
                        this.$nextTick(() => {
                            initCharts();
                        });
                    }
                }))
            });
            document.addEventListener('alpine:init', () => {
                Alpine.data('versionComparison', () => ({
                    activeTab: 'summary'
                }))
            });
        </script>
    </div>
</div>

<script>
function compareVersions() {
    const version1 = document.getElementById('version1').value;
    const version2 = document.getElementById('version2').value;
    window.location.href = `/themes/{{ $theme->id }}/compare/${version1}/${version2}`;
}
</script>
@endsection
