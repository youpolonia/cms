<div class="bg-white rounded-lg shadow overflow-hidden">
    <ul class="divide-y divide-gray-200" id="blocks-list">
        @foreach($blocks as $block)
        <li class="px-6 py-4 flex items-center hover:bg-gray-50" data-id="{{ $block->id }}">
            <div class="handle cursor-move mr-4 text-gray-400 hover:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 3a1 1 0 01.707.293l3 3a1 1 0 01-1.414 1.414L10 5.414 7.707 7.707a1 1 0 01-1.414-1.414l3-3A1 1 0 0110 3zm-3.707 9.293a1 1 0 011.414 0L10 14.586l2.293-2.293a1 1 0 011.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-grow">
                <div class="font-medium">{{ $block->type }}</div>
                <div class="text-sm text-gray-500">{{ $block->content_preview }}</div>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('blocks.edit', ['page' => $block->page, 'block' => $block]) }}" 
                   class="text-indigo-600 hover:text-indigo-900">
                    Edit
                </a>
                <form action="{{ route('blocks.destroy', ['page' => $block->page, 'block' => $block]) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?')">
                        Delete
                    </button>
                </form>
            </div>
        </li>
        @endforeach
    </ul>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const list = document.getElementById('blocks-list');
    new Sortable(list, {
        animation: 150,
        handle: '.handle',
        onEnd: function() {
            const order = Array.from(list.children).map(item => item.dataset.id);
            fetch("{{ route('blocks.reorder', ['page' => $blocks->first()->page]) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ order })
            });
        }
    });
});
</script>
@endpush