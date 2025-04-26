@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Edit Schedule: {{ $contentVersion->content->title }}</h1>

        <div class="mb-6 p-4 bg-gray-50 rounded-md">
            <h2 class="text-lg font-medium mb-2">Current Status</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Version</p>
                    <p>v{{ $contentVersion->version_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Publish Status</p>
                    <p>{{ $contentVersion->is_published ? 'Published' : 'Scheduled' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Publish At</p>
                    <p>{{ $contentVersion->publish_at->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Unpublish At</p>
                    <p>{{ $contentVersion->unpublish_at?->format('Y-m-d H:i') ?? '-' }}</p>
                </div>
            </div>
        </div>

        <form action="{{ route('content.scheduling.update', $contentVersion) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label for="publish_at" class="block text-sm font-medium text-gray-700">New Publish Date & Time</label>
                <input type="datetime-local" name="publish_at" id="publish_at" required
                       value="{{ old('publish_at', $contentVersion->publish_at->format('Y-m-d\TH:i')) }}"
                       min="{{ now()->format('Y-m-d\TH:i') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="unpublish_at" class="block text-sm font-medium text-gray-700">New Unpublish Date & Time (Optional)</label>
                <input type="datetime-local" name="unpublish_at" id="unpublish_at"
                       value="{{ old('unpublish_at', $contentVersion->unpublish_at?->format('Y-m-d\TH:i')) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-sm text-gray-500">Leave blank if content should remain published</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('content.scheduling.index') }}" class="px-4 py-2 border border-gray-300 rounded-md">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">Update Schedule</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.getElementById('publish_at').addEventListener('change', function() {
            const unpublishAt = document.getElementById('unpublish_at');
            if (unpublishAt.value && new Date(unpublishAt.value) <= new Date(this.value)) {
                unpublishAt.value = '';
            }
            unpublishAt.min = this.value;
        });
    </script>
    @endpush
@endsection