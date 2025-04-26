@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Version Comparison</h1>
        <div class="flex space-x-4">
            <a href="{{ route('content.edit', $content->id) }}" class="btn btn-secondary">
                Back to Editor
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="flex justify-between items-center mb-4">
            <div class="text-lg font-semibold">
                Comparing Version #{{ $oldVersion->version_number }} with Version #{{ $newVersion->version_number }}
            </div>
            <div class="text-sm text-gray-600">
                Similarity: {{ $diffData['similarity'] }}%
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="font-medium mb-2">Version #{{ $oldVersion->version_number }}</h3>
                <div class="text-sm text-gray-500 mb-2">
                    Created: {{ $oldVersion->created_at->format('M d, Y H:i') }}
                    by {{ $oldVersion->user->name ?? 'System' }}
                </div>
                <div class="border rounded p-4 bg-gray-50">
                    @foreach($diffData['changes'] as $field => $change)
                        <div class="mb-4">
                            <div class="font-medium">{{ $field }}</div>
                            <div class="diff-old bg-red-50 p-2 rounded text-sm">
                                {!! $change['old_value'] ?? '<span class="text-gray-400">(empty)</span>' !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <h3 class="font-medium mb-2">Version #{{ $newVersion->version_number }}</h3>
                <div class="text-sm text-gray-500 mb-2">
                    Created: {{ $newVersion->created_at->format('M d, Y H:i') }}
                    by {{ $newVersion->user->name ?? 'System' }}
                </div>
                <div class="border rounded p-4 bg-gray-50">
                    @foreach($diffData['changes'] as $field => $change)
                        <div class="mb-4">
                            <div class="font-medium">{{ $field }}</div>
                            <div class="diff-new bg-green-50 p-2 rounded text-sm">
                                {!! $change['new_value'] ?? '<span class="text-gray-400">(empty)</span>' !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-xl font-semibold mb-4">Change Summary</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-blue-600">Characters Added</div>
                <div class="text-2xl font-bold">{{ $diffData['characters_added'] }}</div>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-sm text-red-600">Characters Removed</div>
                <div class="text-2xl font-bold">{{ $diffData['characters_removed'] }}</div>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm text-purple-600">Fields Changed</div>
                <div class="text-2xl font-bold">{{ count($diffData['changes']) }}</div>
            </div>
        </div>

        <div class="flex justify-end space-x-4">
            <form method="POST" action="{{ route('content.versions.restore', $oldVersion->id) }}">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Restore Version #{{ $oldVersion->version_number }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .diff-old ins {
        background-color: #fee2e2;
        text-decoration: none;
    }
    .diff-new del {
        background-color: #dcfce7;
        text-decoration: none;
    }
</style>
@endpush