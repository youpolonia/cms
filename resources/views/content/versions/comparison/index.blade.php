@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Compare Versions: {{ $content->title }}</h1>
        <a href="{{ route('content.show', $content) }}" class="btn btn-secondary">
            Back to Content
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Compare Versions</h2>
        <form action="{{ route('content.versions.comparison.compare', $content) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="version_from" class="block text-sm font-medium text-gray-700 mb-1">From Version</label>
                    <select name="version_from" id="version_from" class="select-input w-full">
                        @foreach($versions as $version)
                            <option value="{{ $version->id }}">Version {{ $version->version_number }} - {{ $version->created_at->format('M d, Y H:i') }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="version_to" class="block text-sm font-medium text-gray-700 mb-1">To Version</label>
                    <select name="version_to" id="version_to" class="select-input w-full">
                        @foreach($versions as $version)
                            <option value="{{ $version->id }}" @if($loop->first) selected @endif>Version {{ $version->version_number }} - {{ $version->created_at->format('M d, Y H:i') }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="btn btn-primary">
                    Compare Versions
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <h2 class="text-xl font-semibold p-6 border-b">Version History</h2>
        <div class="divide-y">
            @foreach($versions as $version)
                <div class="p-6 hover:bg-gray-50">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="font-medium">Version {{ $version->version_number }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ $version->created_at->format('M d, Y H:i') }} by {{ $version->user->name }}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <span class="badge {{ $version->is_autosave ? 'badge-warning' : 'badge-success' }}">
                                {{ $version->is_autosave ? 'Autosave' : 'Published' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="p-4 border-t">
            {{ $versions->links() }}
        </div>
    </div>
</div>
@endsection