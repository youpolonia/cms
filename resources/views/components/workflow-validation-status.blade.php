@props([
    'workflow' => null,
    'theme' => null,
    'isValid' => false,
    'requirements' => []
])

<div 
    x-data="{
        isValid: {{ $isValid ? 'true' : 'false' }},
        requirements: {{ json_encode($requirements) }},
        loading: false,
        checkValidation() {
            this.loading = true;
            fetch('{{ route('api.themes.workflows.validate', [$theme, $workflow]) }}')
                .then(response => response.json())
                .then(data => {
                    this.isValid = data.valid;
                    this.requirements = data.requirements;
                })
                .finally(() => this.loading = false);
        }
    }"
    class="space-y-2"
>
    <div class="flex items-center">
        <span class="text-sm font-medium text-gray-700 mr-2">Validation Status:</span>
        <span 
            x-text="isValid ? 'Valid' : 'Invalid'"
            :class="isValid ? 'text-green-600' : 'text-red-600'"
            class="font-medium"
        ></span>
        <button 
            @click="checkValidation"
            :disabled="loading"
            class="ml-2 inline-flex items-center px-2 py-1 border border-gray-300 text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <span x-show="!loading">Recheck</span>
            <svg x-show="loading" class="animate-spin h-4 w-4 text-gray-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    </div>

    <div x-show="!isValid" class="pl-4">
        <h4 class="text-sm font-medium text-gray-700 mb-1">Missing Requirements:</h4>
        <ul class="list-disc list-inside text-sm text-gray-600">
            <template x-for="req in requirements" :key="req">
                <li x-text="req"></li>
            </template>
        </ul>
    </div>
</div>
