@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Create Approval Step</h1>
        <a href="{{ route('theme-approval-steps.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Back to Steps
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('theme-approval-steps.store') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Step Name</label>
                        <input type="text" name="name" id="name" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" id="description" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                    </div>

                    <div>
                        <label for="approver_role" class="block text-sm font-medium text-gray-700">Approver Role</label>
                        <select name="approver_role" id="approver_role" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">Select a role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="required_approvals" class="block text-sm font-medium text-gray-700">Required Approvals</label>
                        <input type="number" name="required_approvals" id="required_approvals" min="1" value="1" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div>
                        <label for="order" class="block text-sm font-medium text-gray-700">Order</label>
                        <input type="number" name="order" id="order" min="1" required
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_required" id="is_required" value="1"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        <label for="is_required" class="ml-2 block text-sm text-gray-700">Required Step</label>
                    </div>

                    <div>
                        <label for="approval_logic" class="block text-sm font-medium text-gray-700">Approval Logic</label>
                        <select name="approval_logic" id="approval_logic" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="any">Any approval satisfies step</option>
                            <option value="all">All approvals required</option>
                        </select>
                    </div>

                    <div>
                        <label for="rejection_logic" class="block text-sm font-medium text-gray-700">Rejection Logic</label>
                        <select name="rejection_logic" id="rejection_logic" required
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="any">Any rejection fails step</option>
                            <option value="all">All rejections required</option>
                        </select>
                    </div>

                    <div>
                        <label for="timeout_days" class="block text-sm font-medium text-gray-700">Timeout (days)</label>
                        <input type="number" name="timeout_days" id="timeout_days" min="1"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-sm text-gray-500">Number of days before this step times out (optional)</p>
                    </div>

                    <div class="pt-2">
                        <label class="block text-sm font-medium text-gray-700">Requirements</label>
                        <p class="mt-1 text-sm text-gray-500">Additional conditions that must be met before this step can be approved</p>
                        <div id="requirements-container" class="mt-2 space-y-2">
                            <div class="flex">
                                <input type="text" name="requirements[]" placeholder="Enter a requirement"
                                       class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <button type="button" onclick="addRequirementField()" 
                                        class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    +
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2h-1V9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700">
                                    Approval Logic: Controls whether "any" or "all" approvals are needed to pass this step.<br>
                                    Rejection Logic: Controls whether "any" or "all" rejections will fail this step.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Create Step
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@section('scripts')
<script>
function addRequirementField() {
    const container = document.getElementById('requirements-container');
    const newField = document.createElement('div');
    newField.className = 'flex';
    newField.innerHTML = `
        <input type="text" name="requirements[]" placeholder="Enter a requirement"
               class="flex-1 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
        <button type="button" onclick="this.parentNode.remove()" 
                class="ml-2 inline-flex items-center px-3 py-2 border border-gray-300 text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            ×
        </button>
    `;
    container.appendChild(newField);
}

// Initialize with at least one empty requirement field if none exist
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('requirements-container');
    if (container && container.children.length === 0) {
        addRequirementField();
    }
});
</script>
@endsection
