<div class="space-y-6">
    <div>
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category->name ?? '')" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('name')" />
    </div>

    <div>
        <x-input-label for="slug" :value="__('Slug')" />
        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $category->slug ?? '')" required />
        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
    </div>

    <div>
        <x-input-label for="description" :value="__('Description')" />
        <x-textarea id="description" name="description" class="mt-1 block w-full">{{ old('description', $category->description ?? '') }}</x-textarea>
        <x-input-error class="mt-2" :messages="$errors->get('description')" />
    </div>

    <div>
        <label class="flex items-center">
            <x-checkbox name="is_active" :checked="old('is_active', $category->is_active ?? true)" />
            <span class="ml-2 text-sm text-gray-600">Active</span>
        </label>
    </div>

    <div>
        <x-input-label for="seo_title" :value="__('SEO Title')" />
        <x-text-input id="seo_title" name="seo_title" type="text" class="mt-1 block w-full" :value="old('seo_title', $category->seo_title ?? '')" />
        <x-input-error class="mt-2" :messages="$errors->get('seo_title')" />
    </div>

    <div>
        <x-input-label for="seo_description" :value="__('SEO Description')" />
        <x-textarea id="seo_description" name="seo_description" class="mt-1 block w-full">{{ old('seo_description', $category->seo_description ?? '') }}</x-textarea>
        <x-input-error class="mt-2" :messages="$errors->get('seo_description')" />
    </div>
</div>