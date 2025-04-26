@props(['user'])

<div class="mt-4 space-y-2" x-data="{
    threshold: @json($user->getNotificationPreference('theme_comparison_threshold', 0)),
    saving: false,
    error: null,
    async saveThreshold() {
        this.saving = true;
        this.error = null;
        
        try {
            await $wire.updateComparisonThreshold(this.threshold);
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { type: 'success', message: 'Threshold saved' }
            }));
        } catch (e) {
            this.error = 'Failed to save threshold. Please try again.';
            console.error('Save failed:', e);
        } finally {
            this.saving = false;
        }
    }
}">
    <h4 class="text-md font-medium">Theme Comparison Settings</h4>
    
    <div class="flex items-center space-x-2">
        <label for="theme_comparison_threshold" class="whitespace-nowrap">
            Minimum change percentage to notify:
        </label>
        <input 
            type="number" 
            id="theme_comparison_threshold" 
            x-model="threshold"
            min="0" 
            max="100"
            @change.debounce.500ms="saveThreshold"
            class="w-20 rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
        >
        <span>%</span>
    </div>

    <div x-show="saving" class="flex items-center text-sm text-gray-500 mt-2">
        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Saving threshold...
    </div>

    <div x-show="error" x-text="error" class="text-sm text-red-500 mt-2"></div>
</div>
