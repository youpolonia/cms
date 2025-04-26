@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Confirm Theme Rollback</h2>
        
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Rolling back will revert all theme files to this version. Any changes made since this version will be lost.
                    </p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h3 class="text-lg font-medium text-gray-900">Version Details</h3>
            <div class="mt-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-gray-500">Version Number</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $version->version_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Created At</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $version->created_at->format('M d, Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Author</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $version->user->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Changes</p>
                    <p class="mt-1 text-sm text-gray-900">{{ $version->changes_count }} files modified</p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('themes.versions.rollback', ['theme' => $theme->id, 'version' => $version->id]) }}">
            @csrf
            @method('POST')
            
            <div class="flex justify-end space-x-4">
                <a href="{{ route('themes.versions.index', $theme->id) }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Confirm Rollback
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
