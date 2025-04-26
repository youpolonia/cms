@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-4">Review Content: {{ $content->title }}</h1>
        
        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
            <h2 class="text-lg font-semibold mb-2">Content Details</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p><span class="font-medium">Type:</span> {{ $content->type->name }}</p>
                    <p><span class="font-medium">Author:</span> {{ $content->author->name }}</p>
                </div>
                <div>
                    <p><span class="font-medium">Created:</span> {{ $content->created_at->format('M j, Y') }}</p>
                    <p><span class="font-medium">Current Step:</span> {{ $content->currentApprovalStep->name }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <h2 class="text-lg font-semibold mb-2">Content Preview</h2>
            <div class="border rounded-lg p-4">
                {!! $content->body !!}
            </div>
        </div>

        <form action="{{ route('content.decision', $content) }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block font-medium mb-2">Decision</label>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <input type="radio" id="approve" name="decision" value="approved" class="mr-2">
                        <label for="approve">Approve</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" id="reject" name="decision" value="rejected" class="mr-2">
                        <label for="reject">Reject</label>
                    </div>
                    <div class="flex items-center">
                        <input type="radio" id="changes" name="decision" value="changes_requested" class="mr-2">
                        <label for="changes">Request Changes</label>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label for="comments" class="block font-medium mb-2">Comments</label>
                <textarea id="comments" name="comments" rows="4" 
                    class="w-full border rounded-lg p-2"></textarea>
            </div>

            <div id="changesSection" class="mb-6 hidden">
                <label for="changes_requested" class="block font-medium mb-2">Changes Requested</label>
                <textarea id="changes_requested" name="changes_requested" rows="4"
                    class="w-full border rounded-lg p-2"></textarea>
            </div>

            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Submit Decision
            </button>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="decision"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const changesSection = document.getElementById('changesSection');
            changesSection.classList.toggle('hidden', this.value !== 'changes_requested');
        });
    });
</script>
@endsection