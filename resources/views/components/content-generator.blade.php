<div class="bg-white rounded-lg shadow p-6">
    <div class="mb-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">AI Content Generator</h2>
                <p class="text-gray-600 mt-2">Generate content suggestions using AI</p>
            </div>
            <div class="bg-indigo-50 text-indigo-800 px-3 py-1 rounded-full text-sm font-medium">
                <span x-text="remainingCredits"></span> credits remaining
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Template</label>
            <select 
                x-model="template" 
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
            >
                @foreach($templates as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Prompt</label>
            <textarea 
                x-model="prompt" 
                rows="4"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Enter your content prompt..."
            ></textarea>
        </div>

        <div class="flex justify-end space-x-3">
            <button 
                @click="generateContent"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                Generate
            </button>
        </div>

        <div x-show="isLoading" class="text-center py-4">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-600"></div>
        </div>

        <div x-show="suggestions.length > 0" class="mt-6 space-y-4">
            <h3 class="text-lg font-medium text-gray-900">Suggestions</h3>
            <div class="space-y-4">
                <template x-for="(suggestion, index) in suggestions" :key="index">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <p x-text="suggestion" class="whitespace-pre-line"></p>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('contentGenerator', () => ({
                template: 'content_suggestion',
                prompt: '',
                suggestions: [],
                isLoading: false,

                remainingCredits: {{ $remainingCredits }},
                lastUsage: null,
                usageHistory: [],

                generateContent() {
                    if (this.remainingCredits <= 0) {
                        alert('You have no remaining credits. Please contact support.');
                        return;
                    }

                    this.isLoading = true;
                    this.suggestions = [];

                    axios.post('/ai/content/suggestions', {
                        prompt: this.prompt,
                        template: this.template
                    })
                    .then(response => {
                        this.suggestions = response.data.suggestions;
                        this.remainingCredits = response.data.remaining_credits;
                        this.lastUsage = response.data.last_usage;
                        this.usageHistory.unshift({
                            date: new Date().toLocaleString(),
                            prompt: this.prompt,
                            credits_used: response.data.credits_used
                        });
                    })
                    .catch(error => {
                        console.error(error);
                        if (error.response?.status === 429) {
                            alert('Rate limit exceeded. Please wait before generating more content.');
                        } else {
                            alert(error.response?.data?.message || 'Failed to generate content');
                        }
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
                }
            }));
        });
    </script>
</div>