@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Add Media to Collection</h1>
        <x-button.link href="{{ route('media.collections.show', $collection) }}" color="secondary">
            Cancel
        </x-button.link>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('media.collections.attach.store', $collection) }}" method="POST">
            @csrf
            <div class="p-6 space-y-6">
                <div>
                    <x-input.label value="Available Media" />
                    @if($availableMedia->count() > 0)
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mt-2">
                            @foreach($availableMedia as $media)
                                <div class="relative">
                                    <label class="block">
                                        <input 
                                            type="checkbox" 
                                            name="media_ids[]" 
                                            value="{{ $media->id }}" 
                                            class="absolute top-2 left-2 h-5 w-5 rounded border-gray-300 text-primary focus:ring-primary"
                                        >
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
                                        <p class="mt-1 text-sm text-gray-700 truncate">{{ $media->name }}</p>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-gray-50 p-8 text-center rounded-lg">
                            <x-icon name="collection" class="w-12 h-12 mx-auto text-gray-400" />
                            <h3 class="mt-2 text-lg font-medium text-gray-900">No available media</h3>
                            <p class="mt-1 text-sm text-gray-500">All media is already in this collection</p>
                        </div>
                    @endif
                </div>
            </div>

            @if($availableMedia->count() > 0)
                <div class="p-4 border-t flex justify-end space-x-3">
                    <x-button.link href="{{ route('media.collections.show', $collection) }}" color="secondary">
                        Cancel
                    </x-button.link>
                    <x-button type="submit" color="primary">
                        Add Selected
                    </x-button>
                </div>
            @endif
        </form>
    </div>
</div>
@endsection