<div class="p-6 bg-white rounded-lg shadow">
    <h2 class="text-xl font-bold mb-4">Content Editor</h2>
    <div class="rounded border border-dashed border-gray-300 p-4 min-h-[300px]">
        <p class="text-gray-500">Add page content here...</p>
    </div>
    <div class="flex justify-end mt-4">
        <button @click="saveContent()"
            class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Save Changes
        </button>
    </div>
</div>

@push('scripts')
<script>
function saveContent() {
    console.log('Saving content...');
    // Implement proper save functionality here
}
</script>
@endpush