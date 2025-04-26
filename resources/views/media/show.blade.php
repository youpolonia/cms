@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">{{ $media->name }}</h1>
            <p class="text-gray-500 text-sm">{{ $media->created_at->format('M d, Y') }}</p>
        </div>
        <div class="flex space-x-2">
            <x-button.link href="{{ route('media.collections.index', $media) }}" color="secondary">
                <x-icon name="collection" class="mr-2" />
                Collections
            </x-button.link>
            <x-button.link href="#" color="danger" onclick="event.preventDefault(); document.getElementById('delete-form').submit();">
                <x-icon name="trash" class="mr-2" />
                Delete
            </x-button.link>
            <form id="delete-form" action="{{ route('media.destroy', $media) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center">
                    @if($media->type === 'image')
                        <img 
                            src="{{ $media->getUrl('thumb') }}" 
                            alt="{{ $media->name }}"
                            class="w-full h-full object-cover"
                        >
                    @else
                        <x-icon name="document-text" class="w-8 h-8 text-gray-400" />
                    @endif
                </div>
                <div>
                    <h2 class="font-medium">{{ $media->name }}</h2>
                    <p class="text-sm text-gray-500">{{ $media->mime_type }} • {{ $media->human_readable_size }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
            <div class="md:col-span-2">
                @if($media->type === 'image')
                    <img 
                        src="{{ $media->getUrl() }}" 
                        alt="{{ $media->name }}"
                        class="w-full rounded-lg shadow"
                    >
                @else
                    <div class="w-full h-64 bg-gray-100 rounded-lg flex items-center justify-center">
                        <x-icon name="document-text" class="w-16 h-16 text-gray-400" />
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium mb-2">Details</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Type</span>
                            <span>{{ $media->type }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Size</span>
                            <span>{{ $media->human_readable_size }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Dimensions</span>
                            <span>
                                @if($media->width && $media->height)
                                    {{ $media->width }}×{{ $media->height }}
                                @else
                                    N/A
                                @endif
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Uploaded</span>
                            <span>{{ $media->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="font-medium mb-2">Collections</h3>
                    @if($media->collections->count() > 0)
                        <div class="space-y-2">
                            @foreach($media->collections as $collection)
                                <div class="flex justify-between items-center">
                                    <span>{{ $collection->name }}</span>
                                    <form action="{{ route('media.collections.detach', ['media' => $media, 'collection' => $collection]) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-500 hover:text-red-700">
                                            <x-icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">Not in any collections</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
