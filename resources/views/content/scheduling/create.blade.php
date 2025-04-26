@extends('layouts.app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <h1 class="text-2xl font-bold mb-6">Schedule Content: {{ $content->title }}</h1>

        <form action="{{ route('content.scheduling.store', $content) }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label for="version_id" class="block text-sm font-medium text-gray-700">Version</label>
                <select-input name="version_id" id="version_id" required class="mt-1 block w-full">
                    @foreach($versions as $version)
                        <option value="{{ $version->id }}">
                            v{{ $version->version_number }} - {{ $version->created_at->format('Y-m-d H:i') }}
                        </option>
                    @endforeach
                </select-input>
            </div>

            <div>
                <label for="publish_at" class="block text-sm font-medium text-gray-700">Publish Date & Time</label>
                <input type="datetime-local" name="publish_at" id="publish_at" required
                       min="{{ now()->format('Y-m-d\TH:i') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="unpublish_at" class="block text-sm font-medium text-gray-700">Unpublish Date & Time (Optional)</label>
                <input type="datetime-local" name="unpublish_at" id="unpublish_at"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                <p class="mt-1 text-sm text-gray-500">Leave blank if content should remain published</p>
            </div>

            <div class="flex justify-end space-x-4">
                <a href="{{ route('contents.show', $content) }}" class="px-4 py-2 border border-gray-300 rounded-md">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md">Schedule Content</button>
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