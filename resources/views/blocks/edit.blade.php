@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Block: {{ $block->type }}</h1>

    <form action="{{ route('blocks.update', ['page' => $page, 'block' => $block]) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow overflow-hidden p-6 mb-6">
            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Block Type</label>
                    <select name="type" id="type" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach(['text', 'image', 'video', 'quote'] as $type)
                            <option value="{{ $type }}" {{ $block->type === $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div id="content-fields">
                    <!-- Dynamic fields will be loaded here via JavaScript -->
                    @include("blocks.fields.{$block->type}", ['content' => $block->content])
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="btn btn-primary mr-2">
                Update Block
            </button>
            <a href="{{ route('pages.edit', $page) }}" class="btn btn-secondary">
                Cancel
            </a>
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

    // Update fields when type changes
    typeSelect.addEventListener('change', function() {
        loadFields(this.value);
    });
});
</script>
@endpush
@endsection