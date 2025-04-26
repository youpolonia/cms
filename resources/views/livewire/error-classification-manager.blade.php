<div>
    <div class="space-y-4">
        <div>
            <h3 class="text-lg font-medium">Error Classification</h3>
            <p class="text-sm text-gray-500 mt-1">{{ $errorMessage }}</p>
        </div>

        <div class="space-y-2">
            <label class="block text-sm font-medium text-gray-700">Select Category</label>
            <select wire:model="selectedCategory" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                <option value="">Select a category</option>
                @foreach($categories as $category)
                <option value="{{ $category->id }}" style="color: {{ $category->color }}">
                    {{ $category->name }} ({{ ucfirst($category->severity) }})
                </option>
                @endforeach
            </select>

            @if($showSuggestions)
            <div class="mt-2 space-y-1">
                <p class="text-xs text-gray-500">Suggested categories:</p>
                @foreach($suggestedCategories as $category)
                <button wire:click="selectCategory('{{ $category['id'] }}')" 
                        class="px-2 py-1 text-xs rounded-full" 
                        style="background-color: {{ $category['color'] }}20; color: {{ $category['color'] }}">
                    {{ $category['name'] }}
                </button>
                @endforeach
            </div>
            @endif
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea wire:model="classificationNotes" rows="3" class="mt-1 block w-full shadow-sm sm:text-sm focus:ring-indigo-500 focus:border-indigo-500 border border-gray-300 rounded-md"></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <button wire:click="$emit('closeModal')" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                Cancel
            </button>
            <button wire:click="saveClassification" wire:loading.attr="disabled" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <span wire:loading.remove>Save Classification</span>
                <span wire:loading>Saving...</span>
            </button>
        </div>
    </div>
</div>