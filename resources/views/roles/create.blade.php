@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Create New Role</h2>
                
                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">Role Name</label>
                        <input type="text" name="name" id="name" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Permissions</label>
                        <div class="mt-2 space-y-6">
                            @foreach($permissions->groupBy('category') as $category => $categoryPermissions)
                            <div class="space-y-2">
                                @if($category)
                                <h3 class="text-sm font-medium text-gray-500">{{ $category }}</h3>
                                @else
                                <h3 class="text-sm font-medium text-gray-500">Uncategorized</h3>
                                @endif
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pl-4">
                                    @foreach($categoryPermissions as $permission)
                                    <div class="flex items-start">
                                        <div class="flex items-center h-5">
                                            <input id="permission-{{ $permission->id }}" name="permissions[]"
                                                type="checkbox" value="{{ $permission->id }}"
                                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                        </div>
                                        <div class="ml-3 text-sm">
                                            <label for="permission-{{ $permission->id }}" class="font-medium text-gray-700">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn btn-primary">
                            Create Role
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
