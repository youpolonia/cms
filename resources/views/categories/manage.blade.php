@extends('layouts.app')

@push('styles')
<style>
    .draggable {
        cursor: move;
        transition: all 0.3s;
    }
    .draggable.dragging {
        opacity: 0.5;
        background: #f0f9ff;
    }
    .dropzone {
        border: 2px dashed #bfdbfe;
        border-radius: 4px;
        margin: 2px 0;
        transition: all 0.3s;
    }
    .dropzone.active {
        border-color: #3b82f6;
        background: #eff6ff;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Category Management</h1>
    
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">All Categories</h2>
            <div class="flex space-x-2">
                <div class="relative" id="bulk-actions-container" style="display: none">
                    <select id="bulk-action" class="appearance-none bg-gray-100 border border-gray-300 rounded px-4 py-2 pr-8 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Bulk Actions</option>
                        <option value="activate">Activate</option>
                        <option value="deactivate">Deactivate</option>
                        <option value="delete">Delete</option>
                        <option value="move">Move to...</option>
                    </select>
                    <button id="apply-bulk-action" class="ml-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        Apply
                    </button>
                    <div id="move-to-container" class="absolute z-10 mt-2 hidden">
                        <div class="bg-white p-4 rounded shadow-lg border border-gray-200 w-64">
                            <select id="move-to-parent" class="w-full border rounded px-3 py-2">
                                <option value="">Select Parent Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            <button id="confirm-move" class="mt-2 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                Confirm Move
                            </button>
                        </div>
                    </div>
                </div>
                <a href="{{ route('categories.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    Create New Category
                </a>
            </div>
        </div>

        <div class="overflow-x-auto" id="sortable-categories">
            <table class="min-w-full bg-white">
                <colgroup>
                    <col class="w-1/4">
                    <col class="w-1/6">
                    <col class="w-1/6">
                    <col class="w-1/6">
                    <col class="w-1/4">
                </colgroup>
                <thead>
                    <tr>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-sm font-semibold text-gray-700">
                            <input type="checkbox" id="select-all" class="mr-2">
                            Name
                        </th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-sm font-semibold text-gray-700">Slug</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-sm font-semibold text-gray-700">Contents</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-sm font-semibold text-gray-700">Status</th>
                        <th class="py-2 px-4 border-b border-gray-200 bg-gray-50 text-left text-sm font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr class="sortable-item" data-id="{{ $category->id }}" data-depth="{{ $category->depth }}">
                        <td class="py-2 px-4 border-b border-gray-200">
                            <div class="font-medium" style="padding-left: {{ $category->depth * 12 }}px">
                                {{ $category->name }}
                            </div>
                            @if($category->right - $category->left > 1)
                                <div class="text-xs text-gray-500 mt-1">
                                    Subcategories: {{ ($category->right - $category->left - 1) / 2 }}
                                </div>
                            @endif
                        </td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $category->slug }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">{{ $category->contents_count }}</td>
                        <td class="py-2 px-4 border-b border-gray-200">
                            <span class="px-2 py-1 text-xs rounded-full {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $category->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="py-2 px-4 border-b border-gray-200">
                            <div class="flex space-x-2">
                                <a href="{{ route('categories.edit', $category) }}" class="text-blue-500 hover:text-blue-700">
                                    Edit
                                </a>
                                <form action="{{ route('categories.toggle', $category) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-gray-500 hover:text-gray-700">
                                        {{ $category->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form action="{{ route('categories.destroy', $category) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-3">AI Category Organization</h3>
            <form action="{{ route('categories.ai-organize') }}" method="POST">
                @csrf
                <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded">
                    Optimize Category Structure
                </button>
                <p class="text-sm text-gray-500 mt-2">
                    Let AI analyze and suggest better organization for your categories
                </p>
            </form>
        </div>
    </div>
</div>
@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.css">
<style>
.sortable-item {
    cursor: move;
    transition: background-color 0.2s;
}
.sortable-item.sortable-chosen {
    background-color: #f0f9ff;
}
.sortable-ghost {
    opacity: 0.5;
    background: #c8ebfb;
}
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.14.0/Sortable.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const el = document.getElementById('sortable-categories').querySelector('tbody');
    
    new Sortable(el, {
        animation: 150,
        handle: '.sortable-item',
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        onEnd: function() {
            const items = el.querySelectorAll('.sortable-item');
            const updates = Array.from(items).map((item, index) => ({
                id: item.dataset.id,
                depth: parseInt(item.dataset.depth),
                prev_id: index > 0 ? items[index-1].dataset.id : null,
                next_id: index < items.length-1 ? items[index+1].dataset.id : null
            }));
            
            fetch('{{ route("categories.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    updates: updates
                })
            }).catch(error => {
                console.error('Error:', error);
                alert('Failed to save new order');
            });
        }
    });

    // Bulk actions functionality
    document.getElementById('select-all').addEventListener('change', function() {
        document.querySelectorAll('.category-checkbox').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        toggleBulkActions();
    });

    document.querySelectorAll('.category-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', toggleBulkActions);
    });

    function toggleBulkActions() {
        const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
        const container = document.getElementById('bulk-actions-container');
        container.style.display = checkedCount > 0 ? 'block' : 'none';
    }

    document.getElementById('apply-bulk-action').addEventListener('click', function() {
        const action = document.getElementById('bulk-action').value;
        const checkedIds = Array.from(document.querySelectorAll('.category-checkbox:checked'))
            .map(checkbox => checkbox.value);

        if (!action) {
            alert('Please select a bulk action');
            return;
        }

        if (action === 'move') {
            document.getElementById('move-to-container').classList.remove('hidden');
            return;
        }

        performBulkAction(action, checkedIds);
    });

    document.getElementById('confirm-move').addEventListener('click', function() {
        const parentId = document.getElementById('move-to-parent').value;
        const checkedIds = Array.from(document.querySelectorAll('.category-checkbox:checked'))
            .map(checkbox => checkbox.value);

        if (!parentId) {
            alert('Please select a parent category');
            return;
        }

        performBulkAction('move', checkedIds, parentId);
        document.getElementById('move-to-container').classList.add('hidden');
    });

    function performBulkAction(action, ids, parentId = null) {
        fetch('{{ route("categories.bulk-actions") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: action,
                ids: ids,
                parent_id: parentId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Action failed'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to perform bulk action');
        });
    }
});
</script>
@endpush
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    let draggedItem = null;

    // Setup draggable items
    document.querySelectorAll('.draggable').forEach(item => {
        item.addEventListener('dragstart', function() {
            draggedItem = this;
            this.classList.add('dragging');
        });

        item.addEventListener('dragend', function() {
            this.classList.remove('dragging');
        });
    });

    // Setup drop zones
    document.querySelectorAll('.dropzone').forEach(zone => {
        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('active');
        });

        zone.addEventListener('dragleave', function() {
            this.classList.remove('active');
        });

        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('active');
            
            if (draggedItem) {
                const categoryId = draggedItem.dataset.categoryId;
                const newParentId = this.dataset.parentId;
                
                fetch('{{ route("categories.reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        category_id: categoryId,
                        new_parent_id: newParentId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Failed to update category position');
                });
            }
        });
    });
});
</script>
@endpush
