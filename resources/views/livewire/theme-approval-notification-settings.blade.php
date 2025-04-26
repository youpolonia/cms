<div>
    <div class="space-y-4">
        <h3 class="text-lg font-medium">Theme Approval Notifications</h3>
        
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium">Enable Notifications</p>
                <p class="text-xs text-gray-500">Receive notifications when theme versions are approved/rejected</p>
            </div>
            <x-toggle wire:model="enabled" />
        </div>

        <div class="space-y-4 pl-4 border-l-2 border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">Include Comments</p>
                    <p class="text-xs text-gray-500">Show reviewer comments in notifications</p>
                </div>
                <x-toggle wire:model="includeComments" :disabled="!$enabled" />
            </div>

            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">Include Next Steps</p>
                    <p class="text-xs text-gray-500">Show suggested next steps in notifications</p>
                </div>
                <x-toggle wire:model="includeNextSteps" :disabled="!$enabled" />
            </div>
        </div>
    </div>

    @if (session()->has('saved'))
        <x-alert type="success" class="mt-4">
            Settings saved successfully
        </x-alert>
    @endif
</div>
