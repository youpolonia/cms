<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Export') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('exports.store') }}">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 mt-4">
                            <div>
                                <x-label for="start_date" :value="__('Start Date')" />
                                <x-input id="start_date" type="date" class="block mt-1 w-full" name="start_date" required />
                            </div>

                            <div>
                                <x-label for="end_date" :value="__('End Date')" />
                                <x-input id="end_date" type="date" class="block mt-1 w-full" name="end_date" required />
                            </div>

                            <div>
                                <x-label for="format" :value="__('Format')" />
                                <select id="format" name="format" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                    <option value="csv">CSV</option>
                                    <option value="xlsx">Excel</option>
                                    <option value="json">JSON</option>
                                </select>
                            </div>

                            <div>
                                <x-label for="expires_at" :value="__('Expiration Date (Optional)')" />
                                <x-input id="expires_at" type="datetime-local" class="block mt-1 w-full" name="expires_at" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-6">
                            <x-button class="ml-3">
                                {{ __('Create Export') }}
                            </x-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
