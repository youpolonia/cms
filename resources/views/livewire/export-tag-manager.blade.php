<div>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Tags</label>
        <div class="flex flex-wrap gap-2 mb-2">
            @foreach($tags as $tag)
                <label class="inline-flex items-center">
                    <input type="checkbox" 
                        wire:model="selectedTags"
                        value="{{ $tag->id }}"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    >
                    <span class="ml-2 px-2 py-1 rounded text-xs" 
                        style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}">
                        {{ $tag->name }} ({{ $tag->exports_count }})
                    </span>
                </label>
            @endforeach
        </div>
    </div>

    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Search Tags</label>
        <input type="text" 
            wire:model.debounce.300ms="search"
            placeholder="Search existing tags..."
            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
        >
        @if($search)
            <div class="mt-2 space-y-1">
                @foreach($searchResults as $tag)
                    <button type="button" 
                        wire:click="$set('selectedTags', [...selectedTags, {{ $tag->id }}])"
                        class="text-xs px-2 py-1 rounded inline-flex items-center"
                        style="background-color: {{ $tag->color }}20; color: {{ $tag->color }}"
                    >
                        {{ $tag->name }}
                    </button>
                @endforeach
            </div>
        @endif
    </div>

    <div class="border-t pt-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Create New Tag</label>
        <div class="flex gap-2">
            <input type="text" 
                wire:model="newTagName"
                placeholder="Tag name"
                class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <input type="color" 
                wire:model="newTagColor"
                class="w-10 h-10 rounded border border-gray-300"
            >
            <button wire:click="addNewTag"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Add
            </button>
        </div>
    </div>
</div>