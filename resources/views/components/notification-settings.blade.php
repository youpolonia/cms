@props(['user'])

<div class="space-y-4" x-data="{
    preferences: {
        content_approval: @json($user->getNotificationPreference('content_approval', true)),
        content_approval_settings: @json($user->getNotificationPreference('content_approval_settings', {
            step_started: true,
            workflow_completed: true,
            content_rejected: true
        })),
        content_rejection: @json($user->getNotificationPreference('content_rejection', true)),
        content_published: @json($user->getNotificationPreference('content_published', true)),
        theme_approval: @json($user->getNotificationPreference('theme_approval', true)),
        theme_rejection: @json($user->getNotificationPreference('theme_rejection', true)),
        theme_published: @json($user->getNotificationPreference('theme_published', true)),
        theme_rollback: @json($user->getNotificationPreference('theme_rollback', true)),
        theme_update: @json($user->getNotificationPreference('theme_update', true)),
        theme_installed: @json($user->getNotificationPreference('theme_installed', true)),
        theme_comparisons: @json($user->getNotificationPreference('theme_comparisons', true))
    },
    saving: false,
    error: null,
    async savePreferences() {
        this.saving = true;
        this.error = null;
        
        try {
            await $wire.updateNotificationPreferences(this.preferences);
            window.dispatchEvent(new CustomEvent('notify', {
                detail: { type: 'success', message: 'Preferences saved' }
            }));
        } catch (e) {
            this.error = 'Failed to save preferences. Please try again.';
            console.error('Save failed:', e);
        } finally {
            this.saving = false;
        }
    }
}">
    <h3 class="text-lg font-medium">Notification Preferences</h3>
    
    <div class="space-y-2">
        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="content_approval" 
                x-model="preferences.content_approval"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="content_approval" class="ml-2">Content approval notifications</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="content_rejection" 
                x-model="preferences.content_rejection"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="content_rejection" class="ml-2">Content rejection notifications</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="content_published" 
                x-model="preferences.content_published"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="content_published" class="ml-2">Content published notifications</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="theme_approval" 
                x-model="preferences.theme_approval"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="theme_approval" class="ml-2">Theme approval notifications</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="theme_rejection" 
                x-model="preferences.theme_rejection"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="theme_rejection" class="ml-2">Theme rejection notifications</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="theme_published" 
                x-model="preferences.theme_published"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="theme_published" class="ml-2">Theme published notifications</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="theme_rollback" 
                x-model="preferences.theme_rollback"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="theme_rollback" class="ml-2">Theme rollback notifications</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="theme_update" 
                x-model="preferences.theme_update"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="theme_update" class="ml-2">Theme update available notifications</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="theme_installed" 
                x-model="preferences.theme_installed"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="theme_installed" class="ml-2">Theme update installed notifications</label>
        </div>

        <div class="flex items-center">
            <input 
                type="checkbox" 
                id="theme_comparisons" 
                x-model="preferences.theme_comparisons"
                @change.debounce.500ms="savePreferences"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            >
            <label for="theme_comparisons" class="ml-2">Theme comparison notifications</label>
        </div>

        @include('components.comparison-notification-settings-form', ['user' => $user])
        @include('components.update-notification-settings-form', ['user' => $user])
        
        <div x-show="preferences.content_approval" x-transition class="mt-4 pl-4 border-l-2 border-gray-200">
            @include('components.content-approval-notification-settings-form', ['user' => $user])
        </div>

        <div x-show="saving" class="flex items-center text-sm text-gray-500 mt-2">
            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Saving preferences...
        </div>

        <div x-show="error" x-text="error" class="text-sm text-red-500 mt-2"></div>
    </div>
</div>
