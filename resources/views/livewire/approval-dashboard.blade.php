<div>
    <div class="bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-semibold">Approval Dashboard</h2>
            <div class="flex space-x-2">
                <button 
                    wire:click="updateFilter('all')"
                    class="px-4 py-2 text-sm font-medium rounded-md {{ $filter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}"
                >
                    All Items
                </button>
                <button 
                    wire:click="updateFilter('assigned')"
                    class="px-4 py-2 text-sm font-medium rounded-md {{ $filter === 'assigned' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}"
                >
                    My Approvals
                </button>
                <button 
                    wire:click="updateFilter('completed')"
                    class="px-4 py-2 text-sm font-medium rounded-md {{ $filter === 'completed' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700' }}"
                >
                    My Decisions
                </button>
            </div>
        </div>

        <div class="space-y-4">
            @forelse ($items as $item)
                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="font-medium">{{ $item->contentVersion->content->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">
                                Version: {{ $item->contentVersion->version_number }} | 
                                Step: {{ $item->currentStep->name }}
                            </p>
                            <p class="text-sm mt-2">
                                {{ Str::limit($item->contentVersion->version_notes, 150) }}
                            </p>
                        </div>
                        <div class="flex space-x-2">
                            <a 
                                href="{{ route('approvals.show', $item->content_version_id) }}"
                                class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700"
                            >
                                Review
                            </a>
                        </div>
                    </div>
                    @if($item->decisions->count())
                        <div class="mt-3 pt-3 border-t">
                            <h4 class="text-sm font-medium mb-1">Decisions:</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($item->decisions as $decision)
                                    <span class="text-xs px-2 py-1 rounded-full 
                                        {{ $decision->decision === 'approve' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $decision->user->name }}: {{ ucfirst($decision->decision) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-center py-8 text-gray-500">
                    No approval items found
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $items->links() }}
        </div>
    </div>
</div>