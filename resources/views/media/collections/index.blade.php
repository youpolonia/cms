@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Media Collections</h1>
        <x-button.link href="{{ route('media.collections.create') }}" color="primary">
            New Collection
        </x-button.link>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <div class="flex justify-between items-center">
                <x-input.text 
                    name="search" 
                    placeholder="Search collections..." 
                    class="w-full max-w-md"
                />
                <div class="flex space-x-2">
                    <x-select-input 
                        name="sort" 
                        :options="[
                            'newest' => 'Newest First',
                            'oldest' => 'Oldest First',
                            'name_asc' => 'Name (A-Z)',
                            'name_desc' => 'Name (Z-A)'
                        ]" 
                        selected="newest"
                    />
                </div>
            </div>
        </div>

        <div class="divide-y">
            @foreach($collections as $collection)
                <div class="p-4 hover:bg-gray-50 transition-colors duration-150">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                                @if($collection->cover_media)
                                    <img 
                                        src="{{ $collection->cover_media->getUrl('thumb') }}" 
                                        alt="{{ $collection->name }}"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    <x-icon name="collection" class="w-6 h-6 text-gray-400" />
                                @endif
                            </div>
                            <div>
                                <h2 class="font-medium">
                                    <a href="{{ route('media.collections.show', $collection) }}" class="hover:underline">
                                        {{ $collection->name }}
                                    </a>
                                </h2>
                                <p class="text-sm text-gray-500">
                                    {{ $collection->items_count }} items • 
                                    {{ $collection->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <x-button.link 
                                href="{{ route('media.collections.show', $collection) }}" 
                                color="secondary" 
                                size="sm"
                            >
                                View
                            </x-button.link>
                            <x-button.link 
                                href="{{ route('media.collections.destroy', $collection) }}" 
                                color="danger" 
                                size="sm"
                                onclick="event.preventDefault(); document.getElementById('delete-form-{{ $collection->id }}').submit();"
                            >
                                Delete
                            </x-button.link>
                            <form 
                                id="delete-form-{{ $collection->id }}" 
                                action="{{ route('media.collections.destroy', $collection) }}" 
                                method="POST" 
                                class="hidden"
                            >
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-4 border-t">
            {{ $collections->links() }}
        </div>
    </div>
</div>
@endsection
