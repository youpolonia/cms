@props([
    'workflow',
    'theme'
])

<div class="space-y-6">
    <div>
        <h3 class="text-base font-medium">Approval Notifications</h3>
        <p class="text-sm text-gray-500">
            Configure notifications for theme approval requests and status updates
        </p>
    </div>

    <div class="space-y-4">
        <x-checkbox
            wire:model="settings.notify_on_submission"
            label="Notify approvers when new version is submitted"
        />

        <x-checkbox
            wire:model="settings.notify_on_approval"
            label="Notify submitter when version is approved"
        />

        <x-checkbox
            wire:model="settings.notify_on_rejection"
            label="Notify submitter when version is rejected"
        />

        <x-checkbox
            wire:model="settings.notify_on_completion"
            label="Notify all participants when workflow is completed"
        />
    </div>
</div>
