@props(['category' => null, 'categories' => []])

<div class="space-y-6">
    <div>
        <x-label for="name" value="Name" />
        <x-input id="name" name="name" type="text" class="mt-1 block w-full" 
                 value="{{ old('name', $category?->name) }}" required autofocus />
        <x-input-error for="name" class="mt-2" />
    </div>

    <div>
        <x-label for="description" value="Description" />
        <x-textarea id="description" name="description" class="mt-1 block w-full" 
                    rows="3">{{ old('description', $category?->description) }}</x-textarea>
        <x-input-error for="description" class="mt-2" />
    </div>

    <div>
        <x-label for="parent_id" value="Parent Category" />
        <x-select id="parent_id" name="parent_id" class="mt-1 block w-full">
            <option value="">-- No Parent --</option>
            @foreach ($categories as $parent)
                <option value="{{ $parent->id }}" 
                    {{ old('parent_id', $category?->parent_id) == $parent->id ? 'selected' : '' }}>
                    {{ $parent->name }}
                </option>
            @endforeach
        </x-select>
        <x-input-error for="parent_id" class="mt-2" />
    </div>

    <div>
        <x-label for="order" value="Order" />
        <x-input id="order" name="order" type="number" class="mt-1 block w-full" 
                 value="{{ old('order', $category?->order ?? 0) }}" />
        <x-input-error for="order" class="mt-2" />
    </div>

    <div class="flex items-center">
        <x-checkbox id="is_active" name="is_active" 
                   :checked="old('is_active', $category?->is_active ?? true)" />
        <x-label for="is_active" value="Active" class="ml-2" />
    </div>
</div>