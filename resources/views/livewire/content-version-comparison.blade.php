<div>
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-6">Compare Content Versions</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Version 1</label>
                    <select wire:model="version1" class="w-full border rounded p-2">
                        @foreach($content->versions as $version)
                            <option value="{{ $version->id }}">
                                Version {{ $loop->iteration }} - {{ $version->created_at->format('M d, Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Version 2</label>
                    <select wire:model="version2" class="w-full border rounded p-2">
                        @foreach($content->versions as $version)
                            <option value="{{ $version->id }}">
                                Version {{ $loop->iteration }} - {{ $version->created_at->format('M d, Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button wire:click="compare" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-6">
                Compare Versions
            </button>

            @if($diffResult)
                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-4">Content Differences</h2>
                    <div class="border rounded p-4 bg-gray-50">
                        {!! $diffResult !!}
                    </div>
                </div>

                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-4">Metadata Differences</h2>
                    <div class="space-y-2">
                        <p><span class="font-medium">Author Changed:</span> {{ $metadataDiff['author'] ? 'Yes' : 'No' }}</p>
                        <p><span class="font-medium">Created At Changed:</span> {{ $metadataDiff['created_at'] ? 'Yes' : 'No' }}</p>
                        <p><span class="font-medium">Updated At Changed:</span> {{ $metadataDiff['updated_at'] ? 'Yes' : 'No' }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
