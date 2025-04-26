@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Create New Branch for {{ $theme->name }}</h1>
            <a href="{{ route('themes.branches.index', $theme) }}" 
               class="btn btn-secondary">
                Back to Branches
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <form action="{{ route('themes.branches.store', $theme) }}" method="POST">
                @csrf
                <div class="px-6 py-4 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Branch Name</label>
                        <input type="text" name="name" id="name" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                    </div>

                    <div>
                        <label for="base_version_id" class="block text-sm font-medium text-gray-700">Base Version</label>
                        <select name="base_version_id" id="base_version_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            @foreach($versions as $version)
                                <option value="{{ $version->id }}">{{ $version->version }} - {{ $version->created_at->format('Y-m-d') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_protected" id="is_protected"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_protected" class="ml-2 block text-sm text-gray-700">
                            Protect this branch (prevent deletion)
                        </label>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        Create Branch
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
