@props([
    'workflow',
    'theme'
])

<div class="space-y-6">
    <div>
        <h3 class="text-base font-medium">Rollback Notifications</h3>
        <p class="text-sm text-gray-500">
            Configure notifications for theme version rollbacks
        </p>
    </div>

    <div class="space-y-4">
        <x-checkbox
            wire:model="settings.notify_on_initiation"
            label="Notify when rollback is initiated"
        />

        <x-checkbox
            wire:model="settings.notify_on_completion"
            label="Notify when rollback is completed"
        />

        <x-checkbox
            wire:model="settings.notify_affected_users"
            label="Notify all affected users"
        />

        <x-checkbox
            wire:model="settings.include_rollback_reason"
            label="Include rollback reason in notifications"
        />
    </div>
</div>
