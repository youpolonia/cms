@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Version Comparison Results</h1>
        <div class="flex space-x-2">
            <a href="{{ route('content.version-comparison.index', $content) }}" 
               class="btn btn-secondary">
                Back to Comparison
            </a>
            <a href="{{ route('contents.show', $content) }}" 
               class="btn btn-outline">
                Back to Content
            </a>
        </div>
    </div>

    <div class="mb-6 bg-white rounded-lg shadow-md p-4">
        <h2 class="text-lg font-semibold mb-2">{{ $content->title }}</h2>
        <p class="text-gray-600">{{ $diff->summary }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        @livewire('content-version-diff-viewer', ['diff' => $diff])
    </div>

    <div class="mt-6 bg-white rounded-lg shadow-md p-4">
        <div class="flex justify-between items-center">
            <h3 class="font-medium">Comparison Actions</h3>
            <div class="flex space-x-2">
                <a href="{{ route('contents.versions.restore', [
                    'content' => $content,
                    'version' => $toVersion
                ]) }}" 
                   class="btn btn-primary"
                   onclick="return confirm('Restore to version {{ $toVersion->version_number }}?')">
                    Restore This Version
                </a>
                <form action="{{ route('content.version-comparison.destroy', [
                    'content' => $content,
                    'diff' => $diff
                ]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Delete this comparison?')">
                        Delete Comparison
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection