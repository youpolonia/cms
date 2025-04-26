<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
        <input type="text" name="name" id="name" value="{{ old('name', $tag->name ?? '') }}"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            required>
        @error('name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="color" class="block text-sm font-medium text-gray-700">Color</label>
        <div class="mt-1 flex items-center">
            <input type="color" name="color" id="color" value="{{ old('color', $tag->color ?? '#3b82f6') }}"
                class="h-10 w-10 rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <input type="text" name="color" value="{{ old('color', $tag->color ?? '#3b82f6') }}"
                class="ml-2 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                required>
        </div>
        @error('color')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>