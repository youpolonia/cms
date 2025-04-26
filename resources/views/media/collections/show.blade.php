@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $collection->name }}</h1>
            <p class="text-gray-500 text-sm">
                {{ $collection->items->count() }} items • 
                Created {{ $collection->created_at->diffForHumans() }}
            </p>
        </div>
        <div class="flex space-x-2">
            <x-button.link href="{{ route('media.collections.edit', $collection) }}" color="secondary">
                <x-icon name="pencil" class="mr-2" />
                Edit
            </x-button.link>
            <x-button.link href="#" color="danger" onclick="event.preventDefault(); document.getElementById('delete-form').submit();">
                <x-icon name="trash" class="mr-2" />
                Delete
            </x-button.link>
            <form id="delete-form" action="{{ route('media.collections.destroy', $collection) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    @if($collection->description)
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <p class="text-gray-700">{{ $collection->description }}</p>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <div class="flex space-x-4">
                <x-select-input 
                    name="filter" 
                    :options="[
                        'all' => 'All Items',
                        'images' => 'Images',
                        'videos' => 'Videos',
                        'documents' => 'Documents'
                    ]" 
                    selected="all"
                />
            </div>
            <div class="flex space-x-2">
                <x-button.link href="{{ route('media.collections.attach', $collection) }}" color="primary">
                    <x-icon name="plus" class="mr-2" />
                    Add Items
                </x-button.link>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 p-4">
            @foreach($collection->items as $media)
                <div class="relative group">
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                        @if($media->type === 'image')
                            <img 
                                src="{{ $media->getUrl('thumb') }}" 
                                alt="{{ $media->name }}"
                                class="w-full h-full object-cover"
                            >
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <x-icon name="document-text" class="w-12 h-12 text-gray-400" />
                            </div>
                        @endif
                    </div>
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100 space-x-2">
                        <x-button.link 
                            href="{{ route('media.show', $media) }}" 
                            color="white" 
                            size="sm"
                            class="!px-3 !py-1"
                        >
                            View
                        </x-button.link>
                        <form action="{{ route('media.collections.detach', ['media' => $media, 'collection' => $collection]) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-white text-red-500 hover:text-red-700 text-sm px-3 py-1 rounded">
                                Remove
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        @if($collection->items->count() === 0)
            <div class="p-8 text-center">
                <x-icon name="collection" class="w-12 h-12 mx-auto text-gray-400" />
                <h3 class="mt-2 text-lg font-medium text-gray-900">No items in this collection</h3>
                <p class="mt-1 text-sm text-gray-500">Add some media to get started</p>
                <div class="mt-6">
                    <x-button.link href="{{ route('media.collections.attach', $collection) }}" color="primary">
                        <x-icon name="plus" class="mr-2" />
                        Add Items
                    </x-button.link>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
