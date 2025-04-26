<div>
    <div class="mb-6">
        <h2 class="text-lg font-medium text-gray-900">Export Notifications</h2>
        <p class="mt-1 text-sm text-gray-600">Export your notification history to various file formats.</p>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="exportFormat" value="Export Format" />
                <select 
                    id="exportFormat" 
                    wire:model="exportFormat" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                >
                    <option value="csv">CSV</option>
                    <option value="xlsx">Excel</option>
                    <option value="pdf">PDF</option>
                </select>
            </div>

            <div>
                <x-input-label for="dateRange" value="Date Range" />
                <select 
                    id="dateRange" 
                    wire:model="dateRange" 
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                >
                    <option value="last_week">Last Week</option>
                    <option value="last_month">Last Month</option>
                    <option value="last_quarter">Last Quarter</option>
                </select>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label value="Include Status" />
                <div class="mt-2 space-y-2">
                    <label class="inline-flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="includeRead" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Read Notifications</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input 
                            type="checkbox" 
                            wire:model="includeUnread" 
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        >
                        <span class="ml-2 text-sm text-gray-700">Unread Notifications</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="mt-6 flex justify-end">
            @if($exportStatus === 'completed')
                <a 
                    href="{{ $downloadUrl }}" 
                    wire:click.prevent="download"
                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Download Export
                </a>
            @elseif($exportStatus === 'processing')
                <button 
                    type="button" 
                    disabled
                    class="inline-flex items-center px-4 py-2 bg-gray-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none transition ease-in-out duration-150"
                >
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processing...
                </button>
            @else
                <button 
                    type="button" 
                    wire:click="export"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                >
                    Export Notifications
                </button>
            @endif
        </div>
    </div>
</div>