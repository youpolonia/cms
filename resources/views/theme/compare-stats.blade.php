@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">
            Version Statistics: 
            {{ $versionA->version_number }} vs {{ $versionB->version_number }}
        </h1>
        <div class="flex space-x-2">
            <a href="{{ route('themes.compare', [
                'theme' => $versionA->theme,
                'versionA' => $versionA->id,
                'versionB' => $versionB->id
            ]) }}" class="btn btn-outline-primary">
                View Detailed Comparison
            </a>
            <a href="{{ route('themes.history', $versionA->theme) }}" class="btn btn-secondary">
                Back to History
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- File Changes Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">File Changes</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Total Changes</h4>
                    <div class="h-4 bg-gray-200 rounded-full">
                        <div class="h-4 bg-blue-500 rounded-full" 
                             style="width: {{ min(100, $comparison['files_changed']['total_changes'] * 5) }}%">
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $comparison['files_changed']['total_changes'] }} files changed
                    </p>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-green-600 text-2xl font-bold">
                            +{{ $comparison['files_changed']['added']->count() }}
                        </div>
                        <div class="text-sm text-gray-500">Added</div>
                    </div>
                    <div class="text-center">
                        <div class="text-red-600 text-2xl font-bold">
                            -{{ $comparison['files_changed']['removed']->count() }}
                        </div>
                        <div class="text-sm text-gray-500">Removed</div>
                    </div>
                    <div class="text-center">
                        <div class="text-blue-600 text-2xl font-bold">
                            ~{{ $comparison['files_changed']['modified']->count() }}
                        </div>
                        <div class="text-sm text-gray-500">Modified</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Size Changes -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">Size Changes</h3>
            <div class="space-y-4">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Total Size</h4>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            {{ $versionA->total_size_kb ?? 0 }} KB → 
                            {{ $versionB->total_size_kb ?? 0 }} KB
                        </span>
                        <span class="{{ $comparison['size_changes']['total_size_diff'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $comparison['size_changes']['total_size_diff'] >= 0 ? '+' : '' }}{{ $comparison['size_changes']['total_size_diff'] }} KB
                        </span>
                    </div>
                    <div class="h-2 bg-gray-200 rounded-full mt-1">
                        <div class="h-2 {{ $comparison['size_changes']['total_size_diff'] >= 0 ? 'bg-red-500' : 'bg-green-500' }} rounded-full" 
                             style="width: {{ min(100, abs($comparison['size_changes']['total_size_diff']) * 2) }}%">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="font-medium text-gray-700 mb-1">CSS Size</h4>
                        <p class="text-sm {{ $comparison['size_changes']['css_size_diff'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $comparison['size_changes']['css_size_diff'] >= 0 ? '+' : '' }}{{ $comparison['size_changes']['css_size_diff'] }} KB
                        </p>
                    </div>
                    <div>
                        <h4 class="font-medium text-gray-700 mb-1">JS Size</h4>
                        <p class="text-sm {{ $comparison['size_changes']['js_size_diff'] >= 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $comparison['size_changes']['js_size_diff'] >= 0 ? '+' : '' }}{{ $comparison['size_changes']['js_size_diff'] }} KB
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dependency Changes -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h3 class="text-lg font-medium mb-4">Dependency Changes</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <h4 class="font-medium text-green-600 mb-2">Added Dependencies</h4>
                @if(count($comparison['dependencies_changed']['added']) > 0)
                    <ul class="list-disc list-inside text-sm">
                        @foreach($comparison['dependencies_changed']['added'] as $dep)
                        <li>{{ $dep }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">No dependencies added</p>
                @endif
            </div>
            <div>
                <h4 class="font-medium text-red-600 mb-2">Removed Dependencies</h4>
                @if(count($comparison['dependencies_changed']['removed']) > 0)
                    <ul class="list-disc list-inside text-sm">
                        @foreach($comparison['dependencies_changed']['removed'] as $dep)
                        <li>{{ $dep }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">No dependencies removed</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection