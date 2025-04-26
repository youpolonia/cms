<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">{{ $label }}</label>
    <div class="mt-1 flex items-center">
        <div class="relative">
            @if($value)
                <img src="{{ $value }}" alt="Preview" class="h-16 w-16 rounded-md object-cover mr-4" id="{{ $name }}_preview">
            @else
                <div class="h-16 w-16 rounded-md bg-gray-200 flex items-center justify-center text-gray-500 mr-4" id="{{ $name }}_preview">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            @endif
        </div>
        <div class="flex-1">
            <input 
                type="file" 
                name="{{ $name }}" 
                id="{{ $name }}" 
                accept="{{ $accept }}"
                class="block w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4
                    file:rounded-md file:border-0
                    file:text-sm file:font-semibold
                    file:bg-blue-50 file:text-blue-700
                    hover:file:bg-blue-100"
                @if($required) required @endif
                onchange="document.getElementById('{{ $name }}_preview').src = window.URL.createObjectURL(this.files[0])"
            >
            @if($value)
                <button type="button" class="mt-2 text-sm text-red-600 hover:text-red-800" 
                    onclick="document.getElementById('{{ $name }}').value = ''; document.getElementById('{{ $name }}_preview').src = '';">
                    Remove Image
                </button>
            @endif
        </div>
    </div>
</div>