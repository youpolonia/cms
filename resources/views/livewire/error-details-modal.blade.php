<div>
    <!-- Modal Backdrop -->
    @if($showModal)
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50"></div>

    <!-- Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[80vh] flex flex-col">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-medium">Error Details</h3>
                <button @click="showModal = false" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="px-6 py-4 overflow-auto flex-grow">
                <pre class="bg-gray-100 p-4 rounded text-sm whitespace-pre-wrap">{{ $errorDetails }}</pre>
                
                @if($retryStatus)
                <div class="mt-4 p-3 bg-blue-50 text-blue-800 rounded">
                    {{ $retryStatus }}
                </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t flex justify-between">
                <div class="flex gap-3">
                    <button wire:click="copyToClipboard" 
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                        Copy to Clipboard
                    </button>
                    <button wire:click="retryExport" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600 disabled:opacity-50">
                        <span wire:loading.remove>Retry Export</span>
                        <span wire:loading>Processing...</span>
                    </button>
                </div>
                <button @click="showModal = false" 
                        class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                    Close
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Livewire.on('copy-to-clipboard', text => {
            navigator.clipboard.writeText(text).then(() => {
                alert('Error details copied to clipboard');
            });
        });
    });
</script>
@endpush