@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Approval Workflow</h1>
        <a href="{{ route('theme-approvals.workflows.index') }}" 
           class="text-gray-600 hover:text-gray-900">
            Back to Workflows
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('theme-approvals.workflows.update', $workflow) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Workflow Name</label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name', $workflow->name) }}"
                           class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $workflow->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="required_approvers_count" class="block text-sm font-medium text-gray-700">
                            Required Approvers
                        </label>
                        <input type="number" name="required_approvers_count" id="required_approvers_count" min="0" 
                               value="{{ old('required_approvers_count', $workflow->required_approvers_count) }}"
                               class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <div>
                        <label for="sequential_approval" class="flex items-center">
                            <input type="checkbox" name="sequential_approval" id="sequential_approval" value="1"
                                   {{ old('sequential_approval', $workflow->sequential_approval) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Sequential Approval</span>
                        </label>
                    </div>

                    <div>
                        <label for="is_active" class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                   {{ old('is_active', $workflow->is_active) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Active Workflow</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Required Roles</label>
                    <div class="space-y-2">
                        @foreach($roles as $role)
                        <div class="flex items-center">
                            <input type="checkbox" name="required_roles[]" id="role_{{ $role->id }}" value="{{ $role->id }}"
                                   {{ in_array($role->id, old('required_roles', $workflow->required_roles ?? [])) ? 'checked' : '' }}
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="role_{{ $role->id }}" class="ml-2 text-sm text-gray-700">{{ $role->name }}</label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="px-6 py-3 bg-gray-50 text-right">
                <button type="submit" 
                        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded">
                    Update Workflow
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
