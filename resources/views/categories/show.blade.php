@extends('layouts.app', ['title' => $category->name])

@section('content')
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold">{{ $category->name }}</h1>
                    @if($category->description)
                        <p class="mt-2 text-gray-600">{{ $category->description }}</p>
                    @endif
                </div>
                <button id="generate-summary" 
                    class="flex items-center gap-1 px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    AI Summary
                </button>
            </div>
        </div>

        @if($category->children->isNotEmpty())
            <div class="mb-12">
                <h2 class="text-xl font-semibold mb-4">Subcategories</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($category->children as $child)
                        <a href="{{ route('categories.show', $child) }}" 
                           class="block p-4 border rounded-lg hover:bg-gray-50 transition">
                            <h3 class="font-medium">{{ $child->name }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $child->contents_count }} items
                            </p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-4">
                <h2 class="text-xl font-semibold">Content</h2>
                <div class="flex items-center space-x-2">
                    <label for="content_type" class="text-sm text-gray-600">Filter:</label>
                    <select id="content_type" class="text-sm border rounded px-2 py-1">
                        <option value="">All Types</option>
                        <option value="page">Pages</option>
                        <option value="post">Posts</option>
                        <option value="product">Products</option>
                    </select>
                </div>
            </div>

            @if($category->ai_summary)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <h3 class="font-medium text-blue-800 mb-2">AI Summary</h3>
                    <p class="text-blue-700">{{ $category->ai_summary }}</p>
                </div>
            @endif

            @if($contents->isEmpty())
                <p class="text-gray-500">No content available in this category.</p>
            @else
                @if($relatedContents->isNotEmpty())
                    <div class="mb-8">
                        <h3 class="text-lg font-medium mb-4">Related Content You Might Like</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($relatedContents as $content)
                                <div class="border rounded-lg p-4 hover:shadow-md transition">
                                    <h4 class="font-medium">
                                        <a href="{{ route('content.show', $content) }}">{{ $content->title }}</a>
                                    </h4>
                                    <p class="text-sm text-gray-500 mt-1">
                                        {{ $content->created_at->format('M d, Y') }} • 
                                        {{ $content->content_type }}
                                    </p>
                                    <div class="mt-2 text-gray-600 line-clamp-2">
                                        {{ Str::limit(strip_tags($content->content), 150) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="space-y-6" id="content-list">
                    @foreach($contents as $content)
                        <div class="border rounded-lg p-4 hover:shadow-md transition draggable"
                             data-id="{{ $content->id }}" draggable="true">
                            <div class="flex justify-between items-start">
                                <div class="flex-1">
                                <h3 class="text-lg font-medium">
                                    <a href="{{ route('content.show', $content) }}">{{ $content->title }}</a>
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    {{ $content->created_at->format('M d, Y') }} •
                                    {{ $content->content_type }}
                                </p>
                                <div class="mt-2 text-gray-600 line-clamp-2">
                                    {{ Str::limit(strip_tags($content->content), 200) }}
                                </div>
                            </div>
                            <button class="remove-content ml-4 text-red-500 hover:text-red-700"
                                    data-content-id="{{ $content->id }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $contents->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Content type filtering
    const typeFilter = document.getElementById('content_type');
    const contentItems = document.querySelectorAll('#content-list > div');

    typeFilter.addEventListener('change', function() {
        const selectedType = this.value.toLowerCase();
        
        contentItems.forEach(item => {
            const itemType = item.querySelector('.text-sm.text-gray-500')
                .textContent.toLowerCase()
                .split('•')[1]
                .trim();
            
            if (!selectedType || itemType.includes(selectedType)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Drag and drop reordering
    document.getElementById('content-list').addEventListener('dragover', function(e) {
        e.preventDefault();
        const afterElement = getDragAfterElement(this, e.clientY);
        const draggable = document.querySelector('.draggable.dragging');
        if (afterElement == null) {
            this.appendChild(draggable);
        } else {
            this.insertBefore(draggable, afterElement);
        }
    });

    document.querySelectorAll('.draggable').forEach(item => {
        item.addEventListener('dragstart', function() {
            this.classList.add('dragging', 'opacity-50');
        });

        item.addEventListener('dragend', function() {
            this.classList.remove('dragging', 'opacity-50');
            saveNewOrder();
        });
    });

    function getDragAfterElement(container, y) {
        const draggableElements = [...container.querySelectorAll('.draggable:not(.dragging)')];
        return draggableElements.reduce((closest, child) => {
            const box = child.getBoundingClientRect();
            const offset = y - box.top - box.height / 2;
            if (offset < 0 && offset > closest.offset) {
                return { offset: offset, element: child };
            } else {
                return closest;
            }
        }, { offset: Number.NEGATIVE_INFINITY }).element;
    }

    async function saveNewOrder() {
        const contentIds = Array.from(document.querySelectorAll('.draggable'))
            .map(el => el.dataset.id);
        
        try {
            const response = await fetch('{{ route("categories.reorder-contents", $category) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ content_ids: contentIds })
            });

            if (!response.ok) {
                throw new Error('Failed to save order');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to save content order');
        }
    }

    // Remove content from category
    document.querySelectorAll('.remove-content').forEach(button => {
        button.addEventListener('click', async function() {
            if (!confirm('Remove this content from category?')) return;
            
            try {
                const response = await fetch('{{ route("categories.remove-content", $category) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        content_id: this.dataset.contentId
                    })
                });

                if (response.ok) {
                    this.closest('.draggable').remove();
                } else {
                    throw new Error('Failed to remove content');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to remove content from category');
            }
        });
    });

    // AI Summary generation
    document.getElementById('generate-summary').addEventListener('click', async function() {
        const button = this;
        button.disabled = true;
        button.innerHTML = `
            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Generating...
        `;

        try {
            const response = await fetch('{{ route("categories.ai-summary", $category) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            
            if (data.success) {
                location.reload(); // Refresh to show new summary
            } else {
                alert('Failed to generate summary: ' + (data.message || 'Unknown error'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to generate summary');
        } finally {
            button.disabled = false;
            button.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
                AI Summary
            `;
        }
    });
});
</script>
@endpush
