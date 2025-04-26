<div>
    @if($moderationItem)
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Make Approval Decision</h2>
            
            <form wire:submit.prevent="submitDecision">
                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Decision</label>
                    <select wire:model="decision" class="w-full border rounded p-2">
                        <option value="approve">Approve</option>
                        <option value="reject">Reject</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 mb-2">Comments</label>
                    <textarea wire:model="comments" class="w-full border rounded p-2" rows="3"></textarea>
                </div>

                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>Submit Decision</span>
                    <span wire:loading>Processing...</span>
                </button>
            </form>
        </div>

        <div class="mt-6 bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium mb-2">Content Details</h3>
            <p><strong>Title:</strong> {{ $content->title }}</p>
            <p><strong>Version:</strong> {{ $version->version_number }}</p>
            <p><strong>Current Step:</strong> {{ $step->name }}</p>
        </div>
    @endif
</div>