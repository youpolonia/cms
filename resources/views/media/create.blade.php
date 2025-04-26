@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Upload Media</h1>

        <form action="{{ route('media.store') }}" method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6">
            @csrf

            <div class="mb-4">
                <label for="file" class="block text-sm font-medium text-gray-700 mb-2">File</label>
                <input type="file" name="file" id="file" required
                    class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100">
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Collections</label>
                <div class="space-y-2">
                    @foreach($collections as $collection)
                        <div class="flex items-center">
                            <input id="collection-{{ $collection->id }}" name="collections[]" type="checkbox" value="{{ $collection->id }}"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="collection-{{ $collection->id }}" class="ml-2 block text-sm text-gray-700">
                                {{ $collection->name }}
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary">
                    Upload Media
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
