@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit Media</h1>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Media Preview -->
            <div class="p-6 border-b border-gray-200">
                @if(str_starts_with($media->metadata['mime_type'], 'image/'))
                    <img src="{{ Storage::url($media->path) }}" alt="{{ $media->filename }}" class="w-full max-h-64 object-contain">
                @else
                    <div class="bg-gray-100 w-full h-48 flex items-center justify-center">
                        <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                @endif
            </div>

            <!-- Tabs -->
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <a href="#" class="tab-link active" data-tab="details">
                        Details
                    </a>
                    <a href="#" class="tab-link" data-tab="collections">
                        Collections
                    </a>
                    <a href="#" class="tab-link" data-tab="metadata">
                        Metadata
                    </a>
                </nav>
            </div>

            <!-- Tab Content -->
            <form action="{{ route('media.update', $media) }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf
                @method('PUT')

                <!-- Details Tab -->
                <div id="details-tab" class="tab-content active">
                    <div class="mb-4">
                        <label for="filename" class="block text-sm font-medium text-gray-700 mb-2">Filename</label>
                        <input type="text" name="filename" id="filename" value="{{ old('filename', $media->filename) }}"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        @error('filename')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea name="description" id="description" rows="3"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('description', $media->description) }}</textarea>
                    </div>
                </div>

                <!-- Collections Tab -->
                <div id="collections-tab" class="tab-content hidden">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Collections</label>
                        <div class="space-y-2">
                            @foreach($collections as $collection)
                                <div class="flex items-center">
                                    <input id="collection-{{ $collection->id }}" name="collections[]" type="checkbox" 
                                        value="{{ $collection->id }}" {{ $media->collections->contains($collection->id) ? 'checked' : '' }}
                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <label for="collection-{{ $collection->id }}" class="ml-2 block text-sm text-gray-700">
                                        {{ $collection->name }}
                                    </label>
                                    @if($media->collections->contains($collection->id))
                                        <div class="ml-4 flex items-center">
                                            <input id="featured-{{ $collection->id }}" name="featured[]" type="checkbox"
                                                value="{{ $collection->id }}" {{ $media->collections->find($collection->id)->pivot->is_featured ? 'checked' : '' }}
                                                class="h-4 w-4 text-yellow-500 focus:ring-yellow-500 border-gray-300 rounded">
                                            <label for="featured-{{ $collection->id }}" class="ml-2 block text-xs text-gray-500">
                                                Featured
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Metadata Tab -->
                <div id="metadata-tab" class="tab-content hidden">
                    <div class="bg-gray-50 p-4 rounded">
                        <pre class="text-xs">{{ json_encode($media->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const tabName = this.getAttribute('data-tab');

            // Update active tab
            tabLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            // Show corresponding content
            tabContents.forEach(content => {
                content.classList.add('hidden');
                if (content.id === `${tabName}-tab`) {
                    content.classList.remove('hidden');
                }
            });
        });
    });
});
</script>

<style>
.tab-link {
    @apply py-4 px-6 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300;
}
.tab-link.active {
    @apply text-blue-600 border-blue-500;
}
</style>
@endsection
