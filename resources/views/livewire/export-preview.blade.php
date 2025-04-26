<div>
    <div class="mb-6">
        <button wire:click="generateSampleData" class="px-4 py-2 bg-blue-500 text-white rounded">
            Generate Sample Data
        </button>
    </div>

    @if(!empty($items))
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User Agent</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($items as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item['id'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item['timestamp'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">{{ $item['event_type'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ Str::limit($item['user_agent'] ?? '', 30) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $items->links() }}
        </div>
    @else
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        No export data available. Click "Generate Sample Data" to create a preview.
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if(!empty($validation))
        <div class="mt-8">
            <h3 class="text-lg font-medium mb-2">Validation Results</h3>
            <div class="space-y-4">
                @foreach($validation as $type => $result)
                    <div class="p-4 border rounded-lg {{ $result['valid'] ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                        <h4 class="font-medium">{{ ucfirst($type) }} Validation: 
                            <span class="{{ $result['valid'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $result['valid'] ? 'Passed' : 'Failed' }}
                            </span>
                        </h4>
                        @if(!empty($result['errors']))
                            <ul class="mt-2 list-disc list-inside text-sm">
                                @foreach($result['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>