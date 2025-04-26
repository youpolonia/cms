@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Version Comparison</h1>
        <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
            <span>Content: {{ $oldVersion->content->title }}</span>
            <span>•</span>
            <a href="{{ route('content.versions.index', $oldVersion->content) }}" 
               class="text-blue-600 hover:underline">
                Back to versions
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <x-version-comparison 
            :oldVersion="$oldVersion" 
            :newVersion="$newVersion" 
        />

        <div class="border-t border-gray-200 p-4 bg-gray-50">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Comparing versions {{ $oldVersion->version_number }} and {{ $newVersion->version_number }}
                </div>
                <div class="flex space-x-2">
                    @if($oldVersion->previousVersion)
                        <a href="{{ route('content.versions.compare', [$oldVersion->previousVersion, $newVersion]) }}"
                           class="px-3 py-1 bg-gray-100 rounded text-sm">
                            ← Older
                        </a>
                    @endif
                    @if($newVersion->nextVersion)
                        <a href="{{ route('content.versions.compare', [$oldVersion, $newVersion->nextVersion]) }}"
                           class="px-3 py-1 bg-gray-100 rounded text-sm">
                            Newer →
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
