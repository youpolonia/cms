@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Media Library</h1>
        <x-button.link href="{{ route('media.create') }}" color="primary">
            Upload Media
        </x-button.link>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <div class="flex space-x-4">
                <x-select-input 
                    name="filter" 
                    :options="[
                        'all' => 'All Media',
                        'images' => 'Images',
                        'videos' => 'Videos',
                        'documents' => 'Documents'
                    ]" 
                    selected="all"
                />
            </div>
            <div class="flex space-x-2">
                <x-button.link href="#" color="secondary">
                    <x-icon name="filter" class="mr-2" />
                    Filters
                </x-button.link>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 p-4">
            @foreach($media as $item)
                <div class="relative group">
                    <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                        @if($item->type === 'image')
                            <img 
                                src="{{ $item->getUrl('thumb') }}" 
                                alt="{{ $item->name }}"
                                class="w-full h-full object-cover"
                            >
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <x-icon name="document-text" class="w-12 h-12 text-gray-400" />
                            </div>
                        @endif
                    </div>
                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
                        <x-button.link 
                            href="{{ route('media.show', $item) }}" 
                            color="white" 
                            size="sm"
                            class="!px-3 !py-1"
                        >
                            View
                        </x-button.link>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-4 border-t">
            {{ $media->links() }}
        </div>
    </div>
</div>
@endsection
