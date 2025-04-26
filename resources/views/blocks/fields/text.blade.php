<div class="mb-4">
    <label for="content[text]" class="block text-sm font-medium text-gray-700">Text Content</label>
    <textarea name="content[text]" id="content[text]" rows="6" required
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('content.text', $content['text'] ?? '') }}</textarea>
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
            <option value="base" {{ ($content['text_size'] ?? 'base') === 'base' ? 'selected' : '' }}>Normal</option>
            <option value="lg" {{ ($content['text_size'] ?? '') === 'lg' ? 'selected' : '' }}>Large</option>
            <option value="xl" {{ ($content['text_size'] ?? '') === 'xl' ? 'selected' : '' }}>Extra Large</option>
        </select>
    </div>

    <div>
        <label for="content[text_color]" class="block text-sm font-medium text-gray-700">Text Color</label>
        <select name="content[text_color]" id="content[text_color]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="black" {{ ($content['text_color'] ?? 'black') === 'black' ? 'selected' : '' }}>Black</option>
            <option value="gray-700" {{ ($content['text_color'] ?? '') === 'gray-700' ? 'selected' : '' }}>Gray</option>
            <option value="blue-600" {{ ($content['text_color'] ?? '') === 'blue-600' ? 'selected' : '' }}>Blue</option>
        </select>
    </div>
</div>

<div class="flex items-center mb-4">
    <input type="checkbox" name="content[has_background]" id="content[has_background]" value="1" {{ ($content['has_background'] ?? false) ? 'checked' : '' }}
        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
    <label for="content[has_background]" class="ml-2 block text-sm text-gray-700">Add Background</label>
</div>

@if($content['has_background'] ?? false)
<div class="mb-4">
    <label for="content[background_color]" class="block text-sm font-medium text-gray-700">Background Color</label>
    <input type="color" name="content[background_color]" id="content[background_color]" value="{{ $content['background_color'] ?? '#ffffff' }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>
@endif