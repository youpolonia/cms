<div>
    <div wire:sortable="updateStepOrder" wire:sortable.options="{ animation: 150 }">
        @foreach($steps as $step)
        <div wire:sortable.item="{{ $step['id'] }}" wire:key="step-{{ $step['id'] }}" 
             class="mb-3 p-3 border rounded bg-white shadow-sm">
            <div wire:sortable.handle class="cursor-move">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">{{ $step['name'] }}</h5>
                        <p class="mb-0 text-muted">{{ $step['description'] }}</p>
                    </div>
                    <div>
                        <span class="badge bg-primary me-2">Step {{ $step['order'] }}</span>
                        <button wire:click="confirmStepDeletion({{ $step['id'] }})" 
                                class="btn btn-sm btn-danger">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($confirmingStepDeletion)
    <div class="modal d-block" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deletion</h5>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this step?</p>
                </div>
                <div class="modal-footer">
                    <button wire:click="$set('confirmingStepDeletion', false)" 
                            class="btn btn-secondary">
                        Cancel
                    </button>
                    <button wire:click="deleteStep" class="btn btn-danger">
                        Delete Step
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>