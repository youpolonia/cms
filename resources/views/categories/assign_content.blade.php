@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Assign Content to {{ $category->name }}</h1>

    <form action="{{ route('categories.content.store', $category) }}" method="POST">
        @csrf

        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Available Content</h2>
                
                <div class="space-y-4">
                    @foreach($contents as $content)
                    <div class="flex items-center justify-between border-b pb-3">
                        <div class="flex items-center">
                            <input type="checkbox" 
                                   name="contents[]" 
                                   id="content_{{ $content->id }}" 
                                   value="{{ $content->id }}"
                                   {{ $category->contents->contains($content->id) ? 'checked' : '' }}
                                   class="mr-3">
                            <label for="content_{{ $content->id }}" class="font-medium">
                                {{ $content->title }}
                            </label>
                        </div>
                        <div class="w-20">
                            <input type="number" 
                                   name="order[{{ $content->id }}]" 
                                   value="{{ $category->contents->find($content->id)->pivot->order ?? 0 }}" 
                                   class="shadow appearance-none border rounded w-full py-1 px-2 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                   min="0">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-end">
                <a href="{{ route('categories.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded mr-2">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Save Assignments
                </button>
            </div>
        </div>
    </form>
</div>
@endsection