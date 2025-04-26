<div class="mb-4">
    <label for="content[quote_text]" class="block text-sm font-medium text-gray-700">Quote Text</label>
    <textarea name="content[quote_text]" id="content[quote_text]" rows="4" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('content.quote_text', $content['quote_text'] ?? '') }}</textarea>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="content[author]" class="block text-sm font-medium text-gray-700">Author</label>
        <input type="text" name="content[author]" id="content[author]" value="{{ old('content.author', $content['author'] ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="content[source]" class="block text-sm font-medium text-gray-700">Source</label>
        <input type="text" name="content[source]" id="content[source]" value="{{ old('content.source', $content['source'] ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div>
        <label for="content[alignment]" class="block text-sm font-medium text-gray-700">Alignment</label>
        <select name="content[alignment]" id="content[alignment]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="left" {{ ($content['alignment'] ?? 'left') === 'left' ? 'selected' : '' }}>Left</option>
            <option value="center" {{ ($content['alignment'] ?? '') === 'center' ? 'selected' : '' }}>Center</option>
            <option value="right" {{ ($content['alignment'] ?? '') === 'right' ? 'selected' : '' }}>Right</option>
        </select>
    </div>

    <div>
        <label for="content[text_size]" class="block text-sm font-medium text-gray-700">Text Size</label>
        <select name="content[text_size]" id="content[text_size]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="lg" {{ ($content['text_size'] ?? 'lg') === 'lg' ? 'selected' : '' }}>Large</option>
            <option value="xl" {{ ($content['text_size'] ?? '') === 'xl' ? 'selected' : '' }}>Extra Large</option>
            <option value="2xl" {{ ($content['text_size'] ?? '') === '2xl' ? 'selected' : '' }}>XX Large</option>
        </select>
    </div>

    <div>
        <label for="content[text_style]" class="block text-sm font-medium text-gray-700">Style</label>
        <select name="content[text_style]" id="content[text_style]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="normal" {{ ($content['text_style'] ?? 'normal') === 'normal' ? 'selected' : '' }}>Normal</option>
            <option value="italic" {{ ($content['text_style'] ?? '') === 'italic' ? 'selected' : '' }}>Italic</option>
            <option value="bold" {{ ($content['text_style'] ?? '') === 'bold' ? 'selected' : '' }}>Bold</option>
        </select>
    </div>
</div>

<div class="flex items-center mb-4">
    <input type="checkbox" name="content[show_quotes]" id="content[show_quotes]" value="1" {{ ($content['show_quotes'] ?? true) ? 'checked' : '' }}
        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
    <label for="content[show_quotes]" class="ml-2 block text-sm text-gray-700">Show Quote Marks</label>
</div>

<div class="flex items-center">
    <input type="checkbox" name="content[has_border]" id="content[has_border]" value="1" {{ ($content['has_border'] ?? false) ? 'checked' : '' }}
        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
    <label for="content[has_border]" class="ml-2 block text-sm text-gray-700">Add Border</label>
</div>