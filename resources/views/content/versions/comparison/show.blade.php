@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Version Comparison: {{ $content->title }}</h1>
        <a href="{{ route('content.versions.comparison.index', $content) }}" class="btn btn-secondary">
            Back to Version List
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">From Version</h2>
            <div class="space-y-2">
                <p><span class="font-medium">Version:</span> {{ $versionFrom->version_number }}</p>
                <p><span class="font-medium">Created:</span> {{ $versionFrom->created_at->format('M d, Y H:i') }}</p>
                <p><span class="font-medium">By:</span> {{ $versionFrom->user->name }}</p>
                <p>
                    <span class="font-medium">Status:</span>
                    <span class="badge {{ $versionFrom->is_autosave ? 'badge-warning' : 'badge-success' }}">
                        {{ $versionFrom->is_autosave ? 'Autosave' : 'Published' }}
                    </span>
                </p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">To Version</h2>
            <div class="space-y-2">
                <p><span class="font-medium">Version:</span> {{ $versionTo->version_number }}</p>
                <p><span class="font-medium">Created:</span> {{ $versionTo->created_at->format('M d, Y H:i') }}</p>
                <p><span class="font-medium">By:</span> {{ $versionTo->user->name }}</p>
                <p>
                    <span class="font-medium">Status:</span>
                    <span class="badge {{ $versionTo->is_autosave ? 'badge-warning' : 'badge-success' }}">
                        {{ $versionTo->is_autosave ? 'Autosave' : 'Published' }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Comparison Summary</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded p-4">
                <h3 class="font-medium text-gray-700 mb-2">Similarity</h3>
                <div class="flex items-center">
                    <div class="w-full bg-gray-200 rounded-full h-4">
                        <div class="bg-blue-500 h-4 rounded-full" 
                             style="width: {{ $comparison->similarity_score * 100 }}%"></div>
                    </div>
                    <span class="ml-2 font-medium">{{ round($comparison->similarity_score * 100, 1) }}%</span>
                </div>
            </div>

            <div class="bg-gray-50 rounded p-4">
                <h3 class="font-medium text-gray-700 mb-2">Word Count</h3>
                <div class="flex justify-between">
                    <span>{{ $comparison->word_count_changes['from'] }}</span>
                    <span>→</span>
                    <span>{{ $comparison->word_count_changes['to'] }}</span>
                    <span class="{{ $comparison->word_count_changes['difference'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $comparison->word_count_changes['difference'] >= 0 ? '+' : '' }}{{ $comparison->word_count_changes['difference'] }}
                    </span>
                </div>
            </div>

            <div class="bg-gray-50 rounded p-4">
                <h3 class="font-medium text-gray-700 mb-2">Character Count</h3>
                <div class="flex justify-between">
                    <span>{{ $comparison->character_count_changes['from'] }}</span>
                    <span>→</span>
                    <span>{{ $comparison->character_count_changes['to'] }}</span>
                    <span class="{{ $comparison->character_count_changes['difference'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $comparison->character_count_changes['difference'] >= 0 ? '+' : '' }}{{ $comparison->character_count_changes['difference'] }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <h2 class="text-xl font-semibold p-6 border-b">Detailed Changes</h2>
        <div class="divide-y">
            @foreach($comparison->diff_results as $field => $diff)
                <div class="p-6">
                    <h3 class="font-medium mb-2">{{ ucfirst(str_replace('_', ' ', $field)) }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-red-50 p-4 rounded border border-red-200">
                            <h4 class="text-sm font-medium text-red-700 mb-2">Old Value</h4>
                            <div class="text-sm text-red-600 whitespace-pre-wrap">{{ $diff['old_value'] ?? 'N/A' }}</div>
                        </div>
                        <div class="bg-green-50 p-4 rounded border border-green-200">
                            <h4 class="text-sm font-medium text-green-700 mb-2">New Value</h4>
                            <div class="text-sm text-green-600 whitespace-pre-wrap">{{ $diff['new_value'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection