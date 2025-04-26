@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">
            Comparing Versions: 
            {{ $versionA->version_number }} → {{ $versionB->version_number }}
        </h1>
        <a href="{{ route('themes.history', $versionA->theme) }}" class="btn btn-secondary">
            Back to History
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Version Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">Version A</h3>
            <p class="text-sm text-gray-500">
                <strong>Created:</strong> {{ $versionA->created_at->format('M d, Y H:i') }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
                <strong>By:</strong> {{ $versionA->creator->name }}
            </p>
            @if($versionA->notes)
            <div class="mt-3 p-3 bg-gray-50 rounded">
                <p class="text-sm text-gray-700">{{ $versionA->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Stats Summary -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">Changes Summary</h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span>Files Changed:</span>
                    <span class="font-medium">{{ $comparison['files_changed']['total_changes'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Added Files:</span>
                    <span class="text-green-600">{{ $comparison['files_changed']['added']->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Removed Files:</span>
                    <span class="text-red-600">{{ $comparison['files_changed']['removed']->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Modified Files:</span>
                    <span class="text-blue-600">{{ $comparison['files_changed']['modified']->count() }}</span>
                </div>
            </div>
        </div>

        <!-- Version Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-4">Version B</h3>
            <p class="text-sm text-gray-500">
                <strong>Created:</strong> {{ $versionB->created_at->format('M d, Y H:i') }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
                <strong>By:</strong> {{ $versionB->creator->name }}
            </p>
            @if($versionB->notes)
            <div class="mt-3 p-3 bg-gray-50 rounded">
                <p class="text-sm text-gray-700">{{ $versionB->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <!-- File Changes -->
    <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
        <div class="border-b border-gray-200 px-6 py-4">
            <h3 class="text-lg font-medium">File Changes</h3>
        </div>
        
        @if($comparison['files_changed']['added']->isNotEmpty())
        <div class="border-b border-gray-200 px-6 py-4 bg-green-50">
            <h4 class="font-medium text-green-700">Added Files ({{ $comparison['files_changed']['added']->count() }})</h4>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
                @foreach($comparison['files_changed']['added'] as $path => $file)
                <div class="text-sm text-green-600 truncate" title="{{ $path }}">
                    {{ $path }}
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($comparison['files_changed']['removed']->isNotEmpty())
        <div class="border-b border-gray-200 px-6 py-4 bg-red-50">
            <h4 class="font-medium text-red-700">Removed Files ({{ $comparison['files_changed']['removed']->count() }})</h4>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
                @foreach($comparison['files_changed']['removed'] as $path => $file)
                <div class="text-sm text-red-600 truncate" title="{{ $path }}">
                    {{ $path }}
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($comparison['files_changed']['modified']->isNotEmpty())
        <div class="border-b border-gray-200 px-6 py-4 bg-blue-50">
            <h4 class="font-medium text-blue-700">Modified Files ({{ $comparison['files_changed']['modified']->count() }})</h4>
            <div class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-2">
                @foreach($comparison['files_changed']['modified'] as $path => $file)
                <div class="text-sm text-blue-600 truncate" title="{{ $path }}">
                    {{ $path }}
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection