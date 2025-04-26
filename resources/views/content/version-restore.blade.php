@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Restore Version for: {{ $content->title }}</h1>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Version to Restore</h2>
            </div>
            <div class="p-4">
                <div class="font-medium">Version #{{ $version->version_number }}</div>
                <div class="text-sm text-gray-500">
                    {{ $version->created_at->format('M j, Y g:i A') }} by {{ $version->user->name }}
                </div>
                @if($version->notes)
                    <div class="text-sm mt-1 text-gray-600">
                        {{ $version->notes }}
                    </div>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Current Version</h2>
            </div>
            <div class="p-4">
                <div class="font-medium">Version #{{ $currentVersion->version_number }}</div>
                <div class="text-sm text-gray-500">
                    {{ $currentVersion->created_at->format('M j, Y g:i A') }} by {{ $currentVersion->user->name }}
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Confirm Restoration</h2>
            </div>
            <div class="p-4">
                <p class="mb-4">This will create a new version with the content from version #{{ $version->version_number }} and make it the current version.</p>
                
                <form method="POST" action="{{ route('content.versions.restore', [$content, $version]) }}">
                    @csrf
                    <div class="mb-4">
                        <label for="restore_notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Restoration Notes (optional)
                        </label>
                        <textarea name="restore_notes" id="restore_notes" rows="3" 
                                  class="w-full rounded border-gray-300"></textarea>
                    </div>
                    <div class="flex justify-end space-x-4">
                        <a href="{{ route('content.versions.compare', $content) }}" 
                           class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-50">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                            Confirm Restoration
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection