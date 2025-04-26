<div class="theme-builder-container p-6 bg-white rounded-lg shadow">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">AI Theme Builder</h2>
        <p class="text-gray-600">Generate a new theme version using AI</p>
    </div>

    <div class="space-y-6">
        <!-- Prompt Input -->
        <div>
            <label for="prompt" class="block text-sm font-medium text-gray-700 mb-1">
                Describe your theme changes
            </label>
            <textarea
                wire:model="prompt"
                id="prompt"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                placeholder="e.g. 'Make a dark version with blue accents and modern typography'"
            ></textarea>
            @error('prompt') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Generate Button -->
        <button
            wire:click="generateTheme"
            wire:loading.attr="disabled"
            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
            <span wire:loading.remove>Generate Theme</span>
            <span wire:loading>
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Generating...
            </span>
        </button>

        <!-- Error Message -->
        @if($error)
            <div class="p-4 bg-red-50 text-red-700 rounded-md">
                {{ $error }}
            </div>
        @endif

        <!-- Preview Section -->
        @if(!empty($generatedTheme))
            <div class="mt-8 border-t pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Generated Theme Preview</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Theme Config -->
                    <div class="bg-gray-50 p-4 rounded-md">
                        <h4 class="font-medium text-gray-800 mb-2">Configuration</h4>
                        <pre class="text-xs bg-white p-2 rounded overflow-auto max-h-60">{{ json_encode($generatedTheme['config'], JSON_PRETTY_PRINT) }}</pre>
                    </div>

                    <!-- Assets -->
                    <div class="bg-gray-50 p-4 rounded-md">
                        <h4 class="font-medium text-gray-800 mb-2">Assets</h4>
                        <pre class="text-xs bg-white p-2 rounded overflow-auto max-h-60">{{ json_encode($generatedTheme['assets'], JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="mt-6">
                    <button
                        wire:click="saveTheme"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                    >
                        Save as New Version
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
