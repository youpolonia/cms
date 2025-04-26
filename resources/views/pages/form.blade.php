<div class="space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="title" :value="__('Title')" />
            <x-text-input id="title" name="title" type="text" class="mt-1 block w-full" :value="old('title', $page->title ?? '')" required autofocus />
            <x-input-error class="mt-2" :messages="$errors->get('title')" />
        </div>

        <div>
            <x-input-label for="slug" :value="__('Slug')" />
            <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $page->slug ?? '')" required />
            <x-input-error class="mt-2" :messages="$errors->get('slug')" />
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="layout" :value="__('Layout')" />
            <x-select-input id="layout" name="layout" class="mt-1 block w-full" required>
                <option value="default" @selected(old('layout', $page->layout ?? '') == 'default')>Default</option>
                <option value="full-width" @selected(old('layout', $page->layout ?? '') == 'full-width')>Full Width</option>
                <option value="sidebar-left" @selected(old('layout', $page->layout ?? '') == 'sidebar-left')>Sidebar Left</option>
                <option value="sidebar-right" @selected(old('layout', $page->layout ?? '') == 'sidebar-right')>Sidebar Right</option>
            </x-select-input>
            <x-input-error class="mt-2" :messages="$errors->get('layout')" />
        </div>

        <div class="flex items-center space-x-4">
            <div class="flex items-center">
                <x-checkbox-input id="is_published" name="is_published" :checked="old('is_published', $page->is_published ?? false)" />
                <x-input-label for="is_published" :value="__('Published')" class="ml-2" />
            </div>

            <div>
                <x-input-label for="published_at" :value="__('Publish Date')" />
                <x-text-input id="published_at" name="published_at" type="datetime-local" class="mt-1 block w-full" 
                    :value="old('published_at', isset($page->published_at) ? $page->published_at->format('Y-m-d\TH:i') : '')" />
            </div>
        </div>
    </div>

    <div>
        <x-input-label for="meta_title" :value="__('Meta Title')" />
        <x-text-input id="meta_title" name="meta_title" type="text" class="mt-1 block w-full" 
            :value="old('meta_title', $page->meta_title ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('meta_title')" />
    </div>

    <div>
        <x-input-label for="meta_description" :value="__('Meta Description')" />
        <x-textarea-input id="meta_description" name="meta_description" class="mt-1 block w-full" rows="3">
            {{ old('meta_description', $page->meta_description ?? '') }}
        </x-textarea-input>
        <x-input-error class="mt-2" :messages="$errors->get('meta_description')" />
    </div>
</div>