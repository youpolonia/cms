@props([
    'user' => null,
    'themeUpdateSettings' => $user->notificationSettings->theme_updates ?? null
])

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <label for="theme-updates-enabled" class="block text-sm font-medium text-gray-700">
            Enable Theme Update Notifications
        </label>
        <input 
            id="theme-updates-enabled"
            name="theme_updates[enabled]" 
            type="checkbox" 
            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
            {{ $themeUpdateSettings && $themeUpdateSettings['enabled'] ? 'checked' : '' }}
        >
    </div>

    <div class="space-y-2">
        <label class="block text-sm font-medium text-gray-700">
            Notification Channels
        </label>
        <div class="space-y-2">
            <div class="flex items-center">
                <input 
                    id="theme-updates-email" 
                    name="theme_updates[channels][email]" 
                    type="checkbox" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    {{ $themeUpdateSettings && ($themeUpdateSettings['channels']['email'] ?? false) ? 'checked' : '' }}
                >
                <label for="theme-updates-email" class="ml-2 block text-sm text-gray-700">
                    Email
                </label>
            </div>
            <div class="flex items-center">
                <input 
                    id="theme-updates-database" 
                    name="theme_updates[channels][database]" 
                    type="checkbox" 
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                    {{ $themeUpdateSettings && ($themeUpdateSettings['channels']['database'] ?? false) ? 'checked' : '' }}
                >
                <label for="theme-updates-database" class="ml-2 block text-sm text-gray-700">
                    In-App Notifications
                </label>
            </div>
        </div>
    </div>

    <div class="space-y-2">
        <label for="theme-updates-frequency" class="block text-sm font-medium text-gray-700">
            Notification Frequency
        </label>
        <select 
            id="theme-updates-frequency" 
            name="theme_updates[frequency]" 
            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
        >
            <option value="immediate" {{ $themeUpdateSettings && $themeUpdateSettings['frequency'] === 'immediate' ? 'selected' : '' }}>
                Immediately when available
            </option>
            <option value="daily" {{ $themeUpdateSettings && $themeUpdateSettings['frequency'] === 'daily' ? 'selected' : '' }}>
                Daily digest
            </option>
            <option value="weekly" {{ $themeUpdateSettings && $themeUpdateSettings['frequency'] === 'weekly' ? 'selected' : '' }}>
                Weekly digest
            </option>
        </select>
    </div>
</div>
