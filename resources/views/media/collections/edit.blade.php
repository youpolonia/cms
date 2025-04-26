@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Collection</h1>
        <x-button.link href="{{ route('media.collections.show', $collection) }}" color="secondary">
            Cancel
        </x-button.link>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('media.collections.update', $collection) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-6">
                <div>
                    <x-input.label for="name" value="Collection Name" required />
                    <x-input.text 
                        id="name" 
                        name="name" 
                        type="text" 
                        class="w-full mt-1" 
                        value="{{ old('name', $collection->name) }}"
                        required 
                        autofocus
                    />
                    <x-input.error for="name" class="mt-2" />
                </div>

                <div>
                    <x-input.label for="description" value="Description" />
                    <x-textarea 
                        id="description" 
                        name="description" 
                        class="w-full mt-1" 
                        rows="3"
                    >{{ old('description', $collection->description) }}</x-textarea>
                    <x-input.error for="description" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <x-checkbox-input 
                        id="is_private" 
                        name="is_private" 
                        class="mr-2" 
                        :checked="old('is_private', $collection->is_private)"
                    />
                    <x-input.label for="is_private" value="Private Collection" />
                </div>

                <div>
                    <x-input.label for="cover_media_id" value="Cover Image" />
                    <x-select-input 
                        id="cover_media_id" 
                        name="cover_media_id" 
                        :options="$collection->items->mapWithKeys(fn($item) => [$item->id => $item->name])"
                        :selected="old('cover_media_id', $collection->cover_media_id)"
                        placeholder="Select a cover image"
                    />
                    <x-input.error for="cover_media_id" class="mt-2" />
                </div>
            </div>

            <div class="p-4 border-t flex justify-end space-x-3">
                <x-button.link href="{{ route('media.collections.show', $collection) }}" color="secondary">
                    Cancel
                </x-button.link>
                <x-button type="submit" color="primary">
                    Save Changes
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection
