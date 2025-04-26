@foreach($categories as $category)
    <div class="flex items-center py-1 pl-{{ $category->depth * 4 }} hover:bg-gray-50 draggable"
        draggable="true" data-category-id="{{ $category->id }}" data-depth="{{ $category->depth }}">
        <input type="checkbox" 
            name="categories[]" 
            id="category-{{ $category->id }}" 
            value="{{ $category->id }}"
            class="category-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
            @if(in_array($category->id, $selected)) checked @endif
            @if(!$category->is_active) disabled @endif
        >
        <label for="category-{{ $category->id }}" 
            class="ml-2 text-sm {{ $category->is_active ? 'text-gray-700' : 'text-gray-400' }}">
            {{ $category->name }}
            @if(!$category->is_active)
                <span class="text-xs">(inactive)</span>
            @endif
            <span class="ml-1 text-xs text-gray-500">
                ({{ $category->contents_count }})
            </span>
        </label>
    </div>
    
    <div class="dropzone" data-parent-id="{{ $category->id }}"
        style="min-height: 10px; display: {{ $category->right - $category->left > 1 ? 'block' : 'none' }}"></div>
@endforeach
