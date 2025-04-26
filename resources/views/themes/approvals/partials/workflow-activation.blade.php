<div class="bg-white rounded-lg shadow overflow-hidden mb-6">
    <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Workflow Activation</h2>
        
        <form action="{{ route('theme-approvals.workflows.update', $workflow) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" 
                           {{ $workflow->is_active ? 'checked' : '' }}
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-700">
                        Activate this workflow
                    </label>
                </div>

                @if($workflow->is_active)
                <div class="mt-4">
                    <label for="activation_message" class="block text-sm font-medium text-gray-700">
                        Activation Message (Optional)
                    </label>
                    <textarea name="activation_message" id="activation_message" rows="3"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('activation_message', $workflow->activation_message) }}</textarea>
                </div>
                @endif
            </div>

            <div class="mt-6">
                <button type="submit" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Save Activation Status
                </button>
            </div>
        </form>
    </div>
</div>
