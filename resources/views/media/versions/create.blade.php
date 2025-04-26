@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold mb-6">Create New Version for {{ $media->filename }}</h2>

                <form action="{{ route('media.versions.store', $media) }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label for="changes" class="block text-sm font-medium text-gray-700">Changes</label>
                        <textarea id="changes" name="changes" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="comment" class="block text-sm font-medium text-gray-700">Version Comment (Optional)</label>
                        <textarea id="comment" name="comment" rows="2"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                        <p class="mt-1 text-sm text-gray-500">Brief description of what changed in this version</p>
                    </div>

                    <div class="mb-4">
                        <label for="tags" class="block text-sm font-medium text-gray-700">Tags (Optional)</label>
                        <input type="text" id="tags" name="tags" 
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            placeholder="Comma-separated tags (e.g. draft, final, v1)">
                        <p class="mt-1 text-sm text-gray-500">Add tags to help organize versions</p>
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('media.versions.index', $media) }}" 
                            class="mr-4 text-gray-600 hover:text-gray-900">Cancel</a>
                        <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                            Create Version
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
