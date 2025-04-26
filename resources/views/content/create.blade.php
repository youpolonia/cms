@extends('layouts.app', ['title' => 'Create Content'])

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">Create New Content</h1>

        <form action="{{ route('content.store') }}" method="POST" class="space-y-6" id="content-form">
            @csrf

            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" name="title" id="title" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="content_type" class="block text-sm font-medium text-gray-700">Content Type</label>
                <select name="content_type" id="content_type" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="page">Page</option>
                    <option value="post">Blog Post</option>
                    <option value="product">Product</option>
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
                            'selected' => old('categories', isset($content) ? $content->categories->pluck('id')->toArray() : []),
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

            @push('styles')
            <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
            @endpush

            @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script>
                $(document).ready(function() {
                    $('.select2').select2({
                        placeholder: "Select categories",
                        allowClear: true,
                        width: '100%'
                    });
                });
            </script>
            @endpush

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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
            </div>

            <!-- Scheduling Section -->
            <div class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    {{ __('Scheduling Options') }}
                </h3>

                <!-- Publish Date -->
                <div class="mb-4">
                    <x-input-label for="publish_at" :value="__('Publish Date/Time')" />
                    <x-text-input id="publish_at" class="block mt-1 w-full" type="datetime-local" 
                        name="publish_at" value="{{ old('publish_at') }}" />
                    <x-input-error :messages="$errors->get('publish_at')" class="mt-2" />
                </div>

                <!-- Unpublish Date -->
                <div class="mb-4">
                    <x-input-label for="unpublish_at" :value="__('Unpublish Date/Time (optional)')" />
                    <x-text-input id="unpublish_at" class="block mt-1 w-full" type="datetime-local" 
                        name="unpublish_at" value="{{ old('unpublish_at') }}" />
                    <x-input-error :messages="$errors->get('unpublish_at')" class="mt-2" />
                </div>

                <!-- Recurring Content -->
                <div class="mb-4">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="is_recurring" value="1" 
                            class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
                            @checked(old('is_recurring')) />
                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Recurring Content') }}</span>
                    </label>
                </div>

                <!-- Recurring Options (shown when checked) -->
                <div id="recurring-options" class="{{ old('is_recurring') ? '' : 'hidden' }} space-y-4">
                    <div>
                        <x-input-label for="recurrence_pattern" :value="__('Recurrence Pattern')" />
                        <x-text-input id="recurrence_pattern" class="block mt-1 w-full" type="text" 
                            name="recurrence_pattern" value="{{ old('recurrence_pattern') }}" 
                            placeholder="e.g. '0 0 * * *' for daily at midnight" />
                        <x-input-error :messages="$errors->get('recurrence_pattern')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-4 space-x-4">
                <button type="button" id="autosave-btn" 
                    class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-700 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    Save Draft
                </button>

                <x-primary-button>
                    {{ __('Save Content') }}
                </x-primary-button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
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

        // AI Category Suggestions
        document.getElementById('suggest-categories').addEventListener('click', async function() {
            const title = document.getElementById('title').value;
            const content = document.getElementById('content').value;
            
            if (!title && !content) {
                alert('Please enter a title or some content first');
                return;
            }

            try {
                // Show loading state
                button.disabled = true;
                button.innerHTML = '<span class="animate-spin">⏳</span> Generating...';

                try {
                    const response = await fetch('/api/content/generate', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content
                        },
                    body: JSON.stringify({ 
                        prompt: `Suggest relevant categories for content titled "${title}" with content: ${content.substring(0, 200)}...`,
                        type: 'suggest-categories'
                    })
                });

                const data = await response.json();
                
                // Clear current selections
                document.querySelectorAll('.category-checkbox').forEach(checkbox => {
                    checkbox.checked = false;
                });

                // Select suggested categories
                if (data.suggested_categories && data.suggested_categories.length) {
                    data.suggested_categories.forEach(categoryId => {
                        const checkbox = document.querySelector(`.category-checkbox[value="${categoryId}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                    alert('Suggested categories have been selected');
                } else {
                    alert('No category suggestions were returned');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to get category suggestions');
            }
        });

        // AI Content Generation
        document.getElementById('generate-from-categories').addEventListener('click', async function() {
            const selectedCategories = Array.from(document.querySelectorAll('.category-checkbox:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedCategories.length === 0) {
                alert('Please select at least one category first');
                return;
            }

            const title = document.getElementById('title').value;
            const type = document.getElementById('content_type').value;
            const tone = document.getElementById('ai-tone').value;
            const length = document.getElementById('ai-length').value;
                const style = document.getElementById('ai-style').value;
                const translateTo = document.getElementById('ai-translate').value;

            try {
                const response = await fetch('/api/content/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content
                    },
                    body: JSON.stringify({ 
                        type: 'generate-from-categories',
                        categories: selectedCategories,
                        content_type: type,
                        title: title,
                        tone: tone,
                        length: length,
                        style: style,
                        translate_to: translateTo
                    })
                });

                const data = await response.json();
                if (data.success) {
                    document.getElementById('content').value = data.content;
                } else {
                    alert(data.message || 'Failed to generate content');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to generate content from categories');
            }
        });

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
            
            const prompt = `Write a ${type} titled "${title}" in a ${tone} tone (${length} length). Include:
- Clear introduction
- Well-structured sections
- Relevant examples or details
- Strong conclusion
- Tone should be ${tone}
- Length should be ${length}`;

            try {
                const tone = document.getElementById('ai-tone').value;
                const length = document.getElementById('ai-length').value;
                
                const response = await fetch('/api/content/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content
                    },
                    body: JSON.stringify({ 
                        prompt,
                        type: 'generate',
                        content_type: type,
                        tone,
                        length
                    })
                });

                    const data = await response.json();
                    
                    if (!response.ok) {
                        throw new Error(data.message || 'Failed to generate content');
                    }

                    document.getElementById('content').value = data.content;
                    showToast('Content generated successfully!', 'success');
                } catch (error) {
                    console.error('Error:', error);
                    showToast(error.message || 'Failed to generate content', 'error');
                } finally {
                    // Reset button state
                    button.disabled = false;
                    button.textContent = originalText;
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Failed to generate content');
            }
        });

        document.getElementById('improve-content').addEventListener('click', async function() {
            const button = this;
            const originalText = button.textContent;
            button.disabled = true;
            button.innerHTML = '<span class="animate-spin">⏳</span> Improving...';

            const content = document.getElementById('content').value;
            
            if (!content) {
                showToast('Please enter some content first', 'error');
                button.disabled = false;
                button.textContent = originalText;
                return;
            }

            const prompt = `Improve this content while maintaining its original meaning and style. Consider:
- Grammar and clarity
- Flow and structure
- Engagement and readability
- SEO optimization`;

            try {
                const response = await fetch('/api/content/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content
                    },
                    body: JSON.stringify({ 
                        prompt,
                        type: 'improve',
                        current_content: content,
                        tone: document.getElementById('ai-tone').value,
                        style: document.getElementById('ai-style').value
                    })
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to improve content');
                }

                document.getElementById('content').value = data.content;
                showToast('Content improved successfully!', 'success');
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Failed to improve content', 'error');
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        });

        document.getElementById('rewrite-content').addEventListener('click', async function() {
            const button = this;
            const originalText = button.textContent;
            button.disabled = true;
            button.innerHTML = '<span class="animate-spin">⏳</span> Rewriting...';

            const content = document.getElementById('content').value;
            
            if (!content) {
                showToast('Please enter some content first', 'error');
                button.disabled = false;
                button.textContent = originalText;
                return;
            }

            const prompt = `Rewrite this content in a ${document.getElementById('ai-tone').value} tone using a ${document.getElementById('ai-style').value} style. Keep the core meaning but transform the voice and presentation.`;

            try {
                const response = await fetch('/api/content/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content
                    },
                    body: JSON.stringify({ 
                        prompt,
                        type: 'rewrite',
                        current_content: content,
                        tone: document.getElementById('ai-tone').value,
                        style: document.getElementById('ai-style').value,
                        length: document.getElementById('ai-length').value
                    })
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to rewrite content');
                }

                document.getElementById('content').value = data.content;
                showToast('Content rewritten successfully!', 'success');
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Failed to rewrite content', 'error');
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        });

        document.getElementById('summarize-content').addEventListener('click', async function() {
            const button = this;
            const originalText = button.textContent;
            button.disabled = true;
            button.innerHTML = '<span class="animate-spin">⏳</span> Summarizing...';

            const content = document.getElementById('content').value;
            
            if (!content) {
                showToast('Please enter some content first', 'error');
                button.disabled = false;
                button.textContent = originalText;
                return;
            }

            const prompt = `Summarize this content concisely in ${document.getElementById('ai-length').value} length, capturing the key points and main ideas.`;

            try {
                const response = await fetch('/api/content/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content
                    },
                    body: JSON.stringify({ 
                        prompt,
                        type: 'summarize',
                        current_content: content,
                        length: document.getElementById('ai-length').value
                    })
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to summarize content');
                }

                document.getElementById('content').value = data.content;
                showToast('Content summarized successfully!', 'success');
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Failed to summarize content', 'error');
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        });

        document.getElementById('expand-content').addEventListener('click', async function() {
            const button = this;
            const originalText = button.textContent;
            button.disabled = true;
            button.innerHTML = '<span class="animate-spin">⏳</span> Expanding...';

            const content = document.getElementById('content').value;
            
            if (!content) {
                showToast('Please enter some content first', 'error');
                button.disabled = false;
                button.textContent = originalText;
                return;
            }

            const prompt = `Expand this content with more details, examples, and elaboration in a ${document.getElementById('ai-style').value} style. Add depth while maintaining coherence.`;

            try {
                const response = await fetch('/api/content/generate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Authorization': 'Bearer ' + document.querySelector('meta[name="api-token"]').content
                    },
                    body: JSON.stringify({ 
                        prompt,
                        type: 'expand',
                        current_content: content,
                        style: document.getElementById('ai-style').value,
                        length: document.getElementById('ai-length').value
                    })
                });

                const data = await response.json();
                
                if (!response.ok) {
                    throw new Error(data.message || 'Failed to expand content');
                }

                document.getElementById('content').value = data.content;
                showToast('Content expanded successfully!', 'success');
            } catch (error) {
                console.error('Error:', error);
                showToast(error.message || 'Failed to expand content', 'error');
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    </script>
    @endpush
@endsection
