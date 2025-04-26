@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Create New Branch for {{ $media->filename }}</h1>
        <a href="{{ route('media.versions.index', $media) }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
            Back to Versions
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('media.versions.store-branch', $media) }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Branch Name</label>
                <input type="text" id="name" name="name" 
                       class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                       required>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea id="description" name="description" rows="3"
                          class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"></textarea>
            </div>

            <div class="mb-4">
                <label for="base_version_id" class="block text-sm font-medium text-gray-700 mb-1">Base Version</label>
                <select id="base_version_id" name="base_version_id" 
                        class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                        required>
                    @foreach($versions as $version)
                        <option value="{{ $version->id }}">
                            v{{ $version->version_number }} - {{ $version->created_at->format('M d, Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <div class="flex items-center">
                    <input id="is_default" name="is_default" type="checkbox" 
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_default" class="ml-2 block text-sm text-gray-700">
                        Set as default branch
                    </label>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Create Branch
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
