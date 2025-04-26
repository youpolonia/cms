<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-2xl font-bold mb-4">Content Generation</h2>
    
    <form wire:submit.prevent="generateContent">
        <div class="mb-4">
            <label for="prompt" class="block text-sm font-medium text-gray-700">Prompt</label>
            <textarea 
                wire:model="prompt" 
                id="prompt" 
                rows="4"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Enter your content prompt here..."
            ></textarea>
            <div class="mt-1 text-sm text-gray-500">
                Tokens: {{ $tokenCount }} / {{ $maxTokens }}
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            <div>
                <label for="model" class="block text-sm font-medium text-gray-700">Model</label>
                <select 
                    wire:model="model" 
                    id="model"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    @foreach($availableModels as $model => $label)
                        <option value="{{ $model }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="max_tokens" class="block text-sm font-medium text-gray-700">Max Tokens</label>
                <input 
                    type="number" 
                    wire:model="maxTokens" 
                    id="max_tokens"
                    min="100"
                    max="4000"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
            </div>
        </div>

        <div class="flex justify-end">
            <button 
                type="submit" 
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Generate Content</span>
                <span wire:loading>Generating...</span>
            </button>
        </div>
    </form>

    @if($generatedContent)
        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-lg font-medium mb-2">Generated Content</h3>
            <div class="prose max-w-none">
                {!! nl2br(e($generatedContent)) !!}
            </div>
        </div>
    @endif

    @if($error)
        <div class="mt-4 p-4 bg-red-50 text-red-700 rounded-lg">
            {{ $error }}
        </div>
    @endif
</div>
