@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Add Block to: {{ $page->title }}</h1>

    <form action="{{ route('blocks.store', $page) }}" method="POST">
        @csrf

        <div class="bg-white rounded-lg shadow overflow-hidden p-6 mb-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Block Type</label>
                    <select name="type" id="type" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach(['text', 'image', 'video', 'quote'] as $type)
                            <option value="{{ $type }}" {{ old('type') === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="content-fields">
                    <!-- Dynamic fields will be loaded here via JavaScript -->
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary">
                Add Block
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const contentFields = document.getElementById('content-fields');

    function loadFields(type) {
        fetch(`/blocks/fields/${type}`)
            .then(response => response.text())
            .then(html => {
                contentFields.innerHTML = html;
            });
    }

    // Load fields for initial type
    loadFields(typeSelect.value);

    // Update fields when type changes
    typeSelect.addEventListener('change', function() {
        loadFields(this.value);
    });
});
</script>
@endpush
@endsection