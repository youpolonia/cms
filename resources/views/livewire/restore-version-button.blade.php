<div>
    <button 
        wire:click="confirmRestoration"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition"
    >
        Restore This Version
    </button>

    <x-confirmation-modal wire:model="confirmingRestoration">
        <x-slot name="title">
            Confirm Version Restoration
        </x-slot>

        <x-slot name="content">
            <p class="mb-4">Are you sure you want to restore this version? The current content will be replaced.</p>
            
            <div class="mb-4">
                <label for="restorationReason" class="block text-sm font-medium text-gray-700">
                    Reason for Restoration
                </label>
                <textarea
                    wire:model="restorationReason"
                    id="restorationReason"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    rows="3"
                    required
                ></textarea>
                @error('restorationReason')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('confirmingRestoration')">
                Cancel
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="restore">
                Restore Version
            </x-danger-button>
        </x-slot>
    </x-confirmation-modal>
</div>