@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Create New Collection</h1>
        <x-button.link href="{{ route('media.collections.index') }}" color="secondary">
            Cancel
        </x-button.link>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('media.collections.store') }}" method="POST">
            @csrf
            <div class="p-6 space-y-6">
                <div>
                    <x-input.label for="name" value="Collection Name" required />
                    <x-input.text 
                        id="name" 
                        name="name" 
                        type="text" 
                        class="w-full mt-1" 
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
                    />
                    <x-input.error for="description" class="mt-2" />
                </div>

                <div class="flex items-center">
                    <x-checkbox-input 
                        id="is_private" 
                        name="is_private" 
                        class="mr-2" 
                    />
                    <x-input.label for="is_private" value="Private Collection" />
                </div>
            </div>

            <div class="p-4 border-t flex justify-end space-x-3">
                <x-button.link href="{{ route('media.collections.index') }}" color="secondary">
                    Cancel
                </x-button.link>
                <x-button type="submit" color="primary">
                    Create Collection
                </x-button>
            </div>
        </form>
    </div>
</div>
@endsection
