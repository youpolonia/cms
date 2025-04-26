<div class="mb-4">
    @if(isset($content['image_url']))
    <div class="mb-4">
        <img src="{{ $content['image_url'] }}" alt="{{ $content['alt_text'] ?? '' }}" class="max-w-full h-auto rounded-lg">
    </div>
    @endif

    <label for="content[image]" class="block text-sm font-medium text-gray-700">
        {{ isset($content['image_url']) ? 'Replace Image' : 'Upload Image' }}
    </label>
    <input type="file" name="content[image]" id="content[image]" accept="image/*"
        class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="content[alt_text]" class="block text-sm font-medium text-gray-700">Alt Text</label>
        <input type="text" name="content[alt_text]" id="content[alt_text]" value="{{ old('content.alt_text', $content['alt_text'] ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div>
        <label for="content[alignment]" class="block text-sm font-medium text-gray-700">Alignment</label>
        <select name="content[alignment]" id="content[alignment]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="left" {{ ($content['alignment'] ?? 'left') === 'left' ? 'selected' : '' }}>Left</option>
            <option value="center" {{ ($content['alignment'] ?? '') === 'center' ? 'selected' : '' }}>Center</option>
            <option value="right" {{ ($content['alignment'] ?? '') === 'right' ? 'selected' : '' }}>Right</option>
        </select>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
    <div>
        <label for="content[width]" class="block text-sm font-medium text-gray-700">Width</label>
        <select name="content[width]" id="content[width]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="full" {{ ($content['width'] ?? 'full') === 'full' ? 'selected' : '' }}>Full Width</option>
            <option value="half" {{ ($content['width'] ?? '') === 'half' ? 'selected' : '' }}>Half Width</option>
            <option value="third" {{ ($content['width'] ?? '') === 'third' ? 'selected' : '' }}>One Third</option>
        </select>
    </div>

    <div>
        <label for="content[has_border]" class="block text-sm font-medium text-gray-700">Border</label>
        <select name="content[has_border]" id="content[has_border]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="none" {{ ($content['has_border'] ?? 'none') === 'none' ? 'selected' : '' }}>None</option>
            <option value="thin" {{ ($content['has_border'] ?? '') === 'thin' ? 'selected' : '' }}>Thin</option>
            <option value="thick" {{ ($content['has_border'] ?? '') === 'thick' ? 'selected' : '' }}>Thick</option>
        </select>
    </div>
</div>

<div class="flex items-center mb-4">
    <input type="checkbox" name="content[is_rounded]" id="content[is_rounded]" value="1" {{ ($content['is_rounded'] ?? false) ? 'checked' : '' }}
        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
    <label for="content[is_rounded]" class="ml-2 block text-sm text-gray-700">Rounded Corners</label>
</div>

<div class="flex items-center">
    <input type="checkbox" name="content[is_shadowed]" id="content[is_shadowed]" value="1" {{ ($content['is_shadowed'] ?? false) ? 'checked' : '' }}
        class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
    <label for="content[is_shadowed]" class="ml-2 block text-sm text-gray-700">Add Shadow</label>
</div>