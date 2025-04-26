<div>
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium">Rollback Notifications</h3>
            <x-toggle
                wire:model="enabled"
                id="rollback-notifications-enabled"
                label="Enable notifications"
            />
        </div>

        @if($enabled)
            <div class="space-y-6">
                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Notify Users</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-60 overflow-y-auto p-1">
                        @foreach($users as $user)
                            <label class="flex items-center space-x-2">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedUsers"
                                    value="{{ $user->id }}"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                <span>{{ $user->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Notify Roles</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-60 overflow-y-auto p-1">
                        @foreach($roles as $role)
                            <label class="flex items-center space-x-2">
                                <input 
                                    type="checkbox" 
                                    wire:model="selectedRoles"
                                    value="{{ $role->id }}"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                >
                                <span>{{ $role->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-6 flex justify-end">
        <x-button wire:click="save" wire:loading.attr="disabled">
            Save Settings
        </x-button>
    </div>
</div>
