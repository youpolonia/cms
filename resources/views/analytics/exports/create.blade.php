@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Create New Analytics Export</h1>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <form action="{{ route('exports.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Export Name
                        </label>
                        <input type="text" name="name" id="name" 
                            class="w-full rounded-md border-gray-300 shadow-sm" 
                            placeholder="e.g. Monthly Analytics Report">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Start Date
                            </label>
                            <input type="date" name="start_date" id="start_date" 
                                class="w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>

                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                End Date
                            </label>
                            <input type="date" name="end_date" id="end_date" 
                                class="w-full rounded-md border-gray-300 shadow-sm" required>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        Create Export
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection