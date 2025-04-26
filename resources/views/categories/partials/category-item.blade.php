<tr class="hover:bg-gray-50 draggable" data-id="{{ $category->id }}" draggable="true">
    <td class="px-6 py-4 whitespace-nowrap">
        <div class="flex items-center" style="padding-left: {{ $level * 20 }}px">
            <span class="drag-handle mr-2 cursor-move text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                </svg>
            </span>
            @if($category->children->count())
                <button class="toggle-children mr-2 text-gray-500 focus:outline-none">
                    <span class="toggle-icon">▼</span>
                </button>
            @else
                <span class="w-6"></span>
            @endif
            <span class="category-name font-medium">{{ $category->name }}</span>
        </div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
        {{ $category->slug }}
    </td>
    <td class="px-6 py-4 whitespace-nowrap">
        <form action="{{ route('categories.toggle', $category) }}" method="POST">
            @csrf
            @method('PATCH')
            <button type="button" class="toggle-status px-2 py-1 text-xs rounded-full 
                {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                {{ $category->is_active ? 'Active' : 'Inactive' }}
            </button>
        </form>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
        <div class="flex space-x-2">
            <a href="{{ route('categories.edit', $category) }}" class="text-blue-500 hover:text-blue-700">
                Edit
            </a>
            <form action="{{ route('categories.destroy', $category) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-500 hover:text-red-700">
                    Delete
                </button>
            </form>
        </div>
    </td>
</tr>

@if($category->children->count())
    @foreach($category->children as $child)
        @include('categories.partials.category-item', ['category' => $child, 'level' => $level + 1])
    @endforeach
@endif
