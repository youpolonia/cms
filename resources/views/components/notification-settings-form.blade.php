@props([
    'workflow' => null,
    'theme' => null,
    'settings' => []
])

<div 
    x-data="{
        settings: {{ json_encode($settings) }},
        saving: false,
        save() {
            this.saving = true;
            fetch('{{ route('api.themes.workflows.notification-settings', [$theme, $workflow]) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(this.settings)
            })
            .then(response => {
                if (response.ok) {
                    window.dispatchEvent(new CustomEvent('notification-settings-updated', {
                        detail: { workflowId: '{{ $workflow->id }}' }
                    }))
                }
            })
            .finally(() => this.saving = false);
        }
    }"
>
    <div class="space-y-4">
        <div>
            <label class="flex items-center">
                <input 
                    type="checkbox" 
                    x-model="settings.email_enabled"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                <span class="ml-2 text-sm text-gray-700">Email notifications</span>
            </label>
        </div>

        <div>
            <label class="flex items-center">
                <input 
                    type="checkbox" 
                    x-model="settings.in_app_enabled"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                <span class="ml-2 text-sm text-gray-700">In-app notifications</span>
            </label>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Notification recipients</label>
            <input 
                type="text" 
                x-model="settings.recipients"
                placeholder="Comma-separated emails"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
        </div>

        <button
            @click="save"
            :disabled="saving"
            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <span x-show="!saving">Save Settings</span>
            <span x-show="saving" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Saving...
            </span>
        </button>
    </div>
</div>
