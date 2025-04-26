<div>
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Approval Workflows for {{ $contentType->name }}</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Workflow List -->
            <div>
                <h3 class="font-medium text-lg mb-2">Available Workflows</h3>
                <div class="space-y-2">
                    @foreach($workflows as $workflow)
                        <div 
                            wire:click="selectWorkflow({{ $workflow->id }})"
                            class="p-3 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $selectedWorkflow && $selectedWorkflow->id === $workflow->id ? 'border-blue-500 bg-blue-50' : '' }}"
                        >
                            <div class="font-medium">{{ $workflow->name }}</div>
                            <div class="text-sm text-gray-500">{{ $workflow->steps->count() }} steps</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Steps Management -->
            <div>
                @if($selectedWorkflow)
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="font-medium text-lg">Steps for {{ $selectedWorkflow->name }}</h3>
                        <button 
                            wire:click="$toggle('showStepForm')"
                            class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700"
                        >
                            {{ $showStepForm ? 'Cancel' : 'Add Step' }}
                        </button>
                    </div>

                    @if($showStepForm)
                        <div class="bg-gray-50 p-4 rounded-lg mb-4">
                            <h4 class="font-medium mb-2">Add New Step</h4>
                            <form wire:submit.prevent="addStep">
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Name</label>
                                        <input 
                                            type="text" 
                                            wire:model="newStep.name"
                                            class="w-full rounded-md border-gray-300 shadow-sm"
                                        >
                                        @error('newStep.name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea 
                                            wire:model="newStep.description"
                                            class="w-full rounded-md border-gray-300 shadow-sm"
                                            rows="2"
                                        ></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Order</label>
                                        <input 
                                            type="number" 
                                            wire:model="newStep.order"
                                            class="w-full rounded-md border-gray-300 shadow-sm"
                                            min="0"
                                        >
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Approvers</label>
                                        <div class="space-y-2">
                                            <div class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    wire:model="newStep.all_approvers_required"
                                                    id="allApproversRequired"
                                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50"
                                                >
                                                <label for="allApproversRequired" class="ml-2 text-sm text-gray-700">
                                                    Require all approvers
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="pt-2">
                                        <button 
                                            type="submit"
                                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700"
                                        >
                                            Save Step
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="space-y-3">
                        @foreach($steps as $step)
                            <div class="p-3 border rounded-lg bg-gray-50">
                                <div class="font-medium">{{ $step->name }}</div>
                                <div class="text-sm text-gray-500">Order: {{ $step->order }}</div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        Select a workflow to manage its steps
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>