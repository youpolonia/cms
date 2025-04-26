@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Restoration Details</h1>
        <a href="{{ route('admin.restoration-logs.index') }}" class="btn btn-secondary">
            Back to Logs
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-medium mb-2">Content Information</h2>
                    <p><strong>Title:</strong> {{ $log->version->content->title }}</p>
                    <p><strong>Current Version:</strong> #{{ $log->version->content->current_version_id }}</p>
                </div>
                <div>
                    <h2 class="text-lg font-medium mb-2">Restoration Details</h2>
                    <p><strong>Restored Version:</strong> #{{ $log->version->version_number }}</p>
                    <p><strong>Restored At:</strong> {{ $log->created_at->format('M j, Y g:i a') }}</p>
                </div>
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-medium mb-2">User Information</h2>
                <p><strong>Name:</strong> {{ $log->user->name }}</p>
                <p><strong>Email:</strong> {{ $log->user->email }}</p>
                <p><strong>IP Address:</strong> {{ $log->ip_address }}</p>
            </div>

            <div class="mt-6">
                <h2 class="text-lg font-medium mb-2">Restoration Reason</h2>
                <div class="bg-gray-50 p-4 rounded">
                    {{ $log->reason }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection