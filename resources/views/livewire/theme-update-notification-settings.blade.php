@php
    $id = 'theme-update-notifications';
@endphp

<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Theme Update Notifications</h3>
            <p class="text-sm text-gray-500">
                Get notified when theme updates are available
            </p>
        </div>
        <x-toggle
            wire:model="enabled"
            id="{{ $id }}-enabled"
        />
    </div>

    <div x-show="$wire.enabled" x-transition class="pl-8 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <label for="{{ $id }}-email" class="block text-sm font-medium text-gray-700">
                    Email Notifications
                </label>
                <p class="text-sm text-gray-500">
                    Receive email notifications for theme updates
                </p>
            </div>
            <x-toggle
                wire:model="email"
                id="{{ $id }}-email"
            />
        </div>
    </div>

    <div x-show="$wire.enabled" x-transition class="pl-8">
        <x-action-message on="saved" class="text-sm text-green-600">
            Preferences saved.
        </x-action-message>
    </div>
</div>
