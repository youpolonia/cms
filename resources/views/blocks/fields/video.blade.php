<div class="mb-4">
    <label for="content[embed_url]" class="block text-sm font-medium text-gray-700">Video URL</label>
    <input type="url" name="content[embed_url]" id="content[embed_url]" required 
        value="{{ old('content.embed_url', $content['embed_url'] ?? '') }}"
        placeholder="https://www.youtube.com/embed/..." 
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>

@if(isset($content['embed_url']))
<div class="mb-4 aspect-w-16 aspect-h-9">
    <iframe src="{{ $content['embed_url'] }}" frameborder="0" allowfullscreen
        class="w-full h-64 rounded-lg"></iframe>
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
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
        <label for="content[width]" class="block text-sm font-medium text-gray-700">Width</label>
        <select name="content[width]" id="content[width]"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="full" {{ ($content['width'] ?? 'full') === 'full' ? 'selected' : '' }}>Full Width</option>
            <option value="half" {{ ($content['width'] ?? '') === 'half' ? 'selected' : '' }}>Half Width</option>
            <option value="third" {{ ($content['width'] ?? '') === 'third' ? 'selected' : '' }}>One Third</option>
        </select>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
    <div class="flex items-center">
        <input type="checkbox" name="content[autoplay]" id="content[autoplay]" value="1" {{ ($content['autoplay'] ?? false) ? 'checked' : '' }}
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
        <label for="content[autoplay]" class="ml-2 block text-sm text-gray-700">Autoplay</label>
    </div>

    <div class="flex items-center">
        <input type="checkbox" name="content[show_controls]" id="content[show_controls]" value="1" {{ ($content['show_controls'] ?? true) ? 'checked' : '' }}
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
        <label for="content[show_controls]" class="ml-2 block text-sm text-gray-700">Show Controls</label>
    </div>

    <div class="flex items-center">
        <input type="checkbox" name="content[loop]" id="content[loop]" value="1" {{ ($content['loop'] ?? false) ? 'checked' : '' }}
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
        <label for="content[loop]" class="ml-2 block text-sm text-gray-700">Loop</label>
    </div>
</div>

<div class="mb-4">
    <label for="content[caption]" class="block text-sm font-medium text-gray-700">Caption</label>
    <input type="text" name="content[caption]" id="content[caption]" value="{{ old('content.caption', $content['caption'] ?? '') }}"
        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
</div>