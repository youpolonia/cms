@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Version Comparison for: {{ $content->title }}</h1>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Available Versions</h2>
        </div>

        <div class="divide-y">
            @foreach ($versions as $version)
                <div class="p-4 flex items-center justify-between hover:bg-gray-50">
                    <div>
                        <div class="font-medium">
                            Version #{{ $version->version_number }}
                            @if($version->is_autosave)
                                <span class="text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded ml-2">Autosave</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500">
                            {{ $version->created_at->format('M j, Y g:i A') }} by {{ $version->user->name }}
                        </div>
                        @if($version->notes)
                            <div class="text-sm mt-1 text-gray-600">
                                {{ $version->notes }}
                            </div>
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('content.versions.diff', [$content, $version, $versions[0]]) }}" 
                           class="px-3 py-1 bg-blue-100 text-blue-800 rounded text-sm hover:bg-blue-200">
                            Compare with current
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-4 border-t bg-gray-50">
            {{ $versions->links() }}
        </div>
    </div>

    @if($versions->count() > 1)
        <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Compare Two Versions</h2>
            </div>
            <form method="GET" action="{{ route('content.versions.diff', $content) }}" class="p-4">
                <div class="flex space-x-4">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Version 1</label>
                        <select name="version1" class="w-full rounded border-gray-300">
                            @foreach($versions as $version)
                                <option value="{{ $version->id }}">
                                    Version #{{ $version->version_number }} ({{ $version->created_at->format('M j') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Version 2</label>
                        <select name="version2" class="w-full rounded border-gray-300">
                            @foreach($versions as $version)
                                <option value="{{ $version->id }}" @if($loop->first) selected @endif>
                                    Version #{{ $version->version_number }} ({{ $version->created_at->format('M j') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Compare Selected Versions
                </button>
            </form>
        </div>
    @endif
</div>
@endsection