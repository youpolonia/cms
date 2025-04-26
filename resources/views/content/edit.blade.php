@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit: {{ $content->title }}</h1>
        <div class="flex space-x-2">
            <a href="{{ route('content.history', $content) }}"
               class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Version History
            </a>
        </div>
    </div>

    <form action="{{ route('content.update', $content) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div>
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input type="text" name="title" id="title" required value="{{ old('title', $content->title) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        </div>

        <div>
            <label for="content_type" class="block text-sm font-medium text-gray-700">Content Type</label>
            <select name="content_type" id="content_type" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <option value="page" @selected(old('content_type', $content->content_type) === 'page')>Page</option>
                <option value="post" @selected(old('content_type', $content->content_type) === 'post')>Blog Post</option>
                <option value="product" @selected(old('content_type', $content->content_type) === 'product')>Product</option>
            </select>
        </div>

        <div>
            <div class="flex items-center justify-between">
                <label for="categories" class="block text-sm font-medium text-gray-700">Categories</label>
                <button type="button" id="suggest-categories"
                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">
                    Suggest Categories with AI
                </button>
            </div>
            <div class="mt-1 space-y-2">
                <div class="bg-white rounded-md border border-gray-300 p-4 max-h-60 overflow-y-auto">
                    @include('categories.partials.category-tree-select', [
                        'categories' => $categories,
                        'selected' => old('categories', $content->categories->pluck('id')->toArray()),
                        'level' => 0
                    ])
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" id="select-all-categories" class="text-xs text-blue-600 hover:text-blue-800">
                        Select All
                    </button>
                    <button type="button" id="deselect-all-categories" class="text-xs text-red-600 hover:text-red-800">
                        Deselect All
                    </button>
                </div>
            </div>
            @error('categories')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
            <div class="flex flex-wrap gap-2 mb-2">
                <button type="button" id="generate-outline" class="bg-indigo-500 text-white px-3 py-1 rounded text-sm">
                    Generate Outline
                </button>
                <button type="button" id="generate-content" class="bg-blue-500 text-white px-3 py-1 rounded text-sm">
                    Generate Full Content
                </button>
                <button type="button" id="generate-from-categories" class="bg-teal-500 text-white px-3 py-1 rounded text-sm">
                    Generate From Categories
                </button>
                <button type="button" id="improve-content" class="bg-green-500 text-white px-3 py-1 rounded text-sm">
                    Improve
                </button>
                <button type="button" id="rewrite-content" class="bg-purple-500 text-white px-3 py-1 rounded text-sm">
                    Rewrite
                </button>
                <button type="button" id="summarize-content" class="bg-yellow-500 text-white px-3 py-1 rounded text-sm">
                    Summarize
                </button>
                <button type="button" id="expand-content" class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                    Expand
                </button>
                <select id="ai-tone" class="text-sm border rounded px-2 py-1">
                    <option value="professional">Professional</option>
                    <option value="casual">Casual</option>
                    <option value="friendly">Friendly</option>
                    <option value="authoritative">Authoritative</option>
                    <option value="humorous">Humorous</option>
                    <option value="technical">Technical</option>
                </select>
                <select id="ai-length" class="text-sm border rounded px-2 py-1">
                    <option value="short">Short</option>
                    <option value="medium">Medium</option>
                    <option value="long">Long</option>
                </select>
                <select id="ai-style" class="text-sm border rounded px-2 py-1">
                    <option value="concise">Concise</option>
                    <option value="detailed">Detailed</option>
                    <option value="storytelling">Storytelling</option>
                    <option value="persuasive">Persuasive</option>
                </select>
                <select id="ai-translate" class="text-sm border rounded px-2 py-1">
                    <option value="">Translate To</option>
                    <option value="es">Spanish</option>
                    <option value="fr">French</option>
                    <option value="de">German</option>
                    <option value="it">Italian</option>
                    <option value="pt">Portuguese</option>
                    <option value="ru">Russian</option>
                    <option value="zh">Chinese</option>
                    <option value="ja">Japanese</option>
                    <option value="ko">Korean</option>
                </select>
            </div>
            <textarea name="content" id="content" rows="10" required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('content', $content->content) }}</textarea>
        </div>

        <!-- Scheduling Section -->
        <div class="space-y-4">
            <div class="flex items-center">
                <input type="checkbox" name="is_scheduled" id="is_scheduled" value="1"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    @checked(old('is_scheduled', $content->is_scheduled)) />
                <label for="is_scheduled" class="ml-2 block text-sm font-medium text-gray-700">
                    Schedule this content
                </label>
            </div>

            <div id="scheduling-fields" class="{{ old('is_scheduled', $content->is_scheduled) ? '' : 'hidden' }} space-y-4">
                <!-- Publish Date -->
                <div>
                    <label for="publish_at" class="block text-sm font-medium text-gray-700">Publish Date/Time</label>
                    <input type="datetime-local" name="publish_at" id="publish_at"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value="{{ old('publish_at', $content->publish_at ? $content->publish_at->format('Y-m-d\TH:i') : '') }}">
                    @error('publish_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Expire Date -->
                <div>
                    <label for="expire_at" class="block text-sm font-medium text-gray-700">Expire Date/Time (optional)</label>
                    <input type="datetime-local" name="expire_at" id="expire_at"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                        value="{{ old('expire_at', $content->expire_at ? $content->expire_at->format('Y-m-d\TH:i') : '') }}">
                    @error('expire_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end mt-4 space-x-4">
            <button type="button" id="autosave-btn"
                class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Save Draft
            </button>

            <x-primary-button>
                {{ __('Update Content') }}
            </x-primary-button>
        </div>
    </form>
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    // Show/hide scheduling fields based on checkbox
    document.getElementById('is_scheduled').addEventListener('change', function() {
        document.getElementById('scheduling-fields').classList.toggle('hidden', !this.checked);
    });

    // Category selection controls
    document.getElementById('select-all-categories').addEventListener('click', function() {
        document.querySelectorAll('.category-checkbox:not(:disabled)').forEach(checkbox => {
            checkbox.checked = true;
        });
    });

    document.getElementById('deselect-all-categories').addEventListener('click', function() {
        document.querySelectorAll('.category-checkbox:not(:disabled)').forEach(checkbox => {
            checkbox.checked = false;
        });
    });

    // AI Content Generation
    document.getElementById('generate-content').addEventListener('click', async function() {
        const button = this;
        const originalText = button.textContent;
        button.disabled = true;
        button.innerHTML = '<span class="animate-spin">⏳</span> Generating...';

        const title = document.getElementById('title').value;
        const type = document.getElementById('content_type').value;
        
        if (!title) {
            alert('Please enter a title first');
            button.disabled = false;
            button.textContent = originalText;
            return;
        }

        const tone = document.getElementById('ai-tone').value;
        const length = document.getElementById('ai-length').value;
        
        try {
            const response = await fetch('/api/content/generate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    title: title,
                    content_type: type,
                    tone: tone,
                    length: length
                })
            });

            const data = await response.json();
            document.getElementById('content').value = data.content;
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to generate content');
        } finally {
            button.disabled = false;
            button.textContent = originalText;
        }
    });

    // Initialize Select2
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select categories",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush

@endsection
