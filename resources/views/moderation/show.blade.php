@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Moderation Review</h1>
            <div class="flex gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold
                    {{ $moderation->priority > 7 ? 'bg-red-100 text-red-800' :
                       ($moderation->priority > 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                    Priority: {{ $moderation->priority }}
                </span>
                @if($moderation->flag_severity)
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                    {{ ucfirst($moderation->flag_severity) }} Flags
                </span>
                @endif
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div>
                <h2 class="text-lg font-semibold mb-2">Content Details</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">ID:</span> {{ $moderation->content_id }}</p>
                    <p><span class="font-medium">Submitted:</span> {{ $moderation->created_at->format('M d, Y H:i') }}</p>
                    <p><span class="font-medium">Submitted By:</span> {{ $moderation->user->name ?? 'Unknown' }}</p>
                </div>
            </div>
            
            <div>
                <h2 class="text-lg font-semibold mb-2">Content Preview</h2>
                <div class="border rounded p-4 bg-gray-50 max-h-96 overflow-y-auto">
                    <h3 class="font-medium mb-2">{{ $moderation->content->title }}</h3>
                    <p class="text-sm text-gray-500 mb-2">Type: {{ $moderation->content->content_type }}</p>
                    <div class="prose max-w-none">
                        {!! $moderation->content->content !!}
                    </div>
                </div>
            </div>
        </div>

        @if($moderation->status === 'pending')
        <div class="border-t pt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">AI Moderation Suggestions</h2>
                @if($moderation->automated_flags)
                <div class="text-sm">
                    <span class="font-medium">Auto-Flags:</span>
                    @foreach($moderation->automated_flags as $flag => $value)
                        <span class="inline-block bg-gray-100 rounded px-2 py-1 text-xs ml-2">
                            {{ $flag }}: {{ $value }}
                        </span>
                    @endforeach
                </div>
                @endif
            </div>
            <div class="mb-6 p-4 bg-blue-50 rounded-lg">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-medium">AI Analysis</span>
                    @if($moderation->is_ai_generated)
                        <span class="px-2 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-800">
                            AI-Generated
                        </span>
                    @endif
                </div>

                @if($moderation->openai_moderation_results)
                    <div class="mb-4">
                        <h3 class="font-medium text-sm mb-2">OpenAI Moderation Results</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($moderation->openai_moderation_results['categories'] ?? [] as $category => $flagged)
                                @if($flagged)
                                    <div class="flex items-center gap-2 p-2 bg-red-50 rounded">
                                        <span class="text-red-500">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </span>
                                        <span class="text-sm capitalize">{{ str_replace('_', ' ', $category) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($moderation->requires_human_review)
                    <div class="p-3 bg-yellow-50 border-l-4 border-yellow-400 mb-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </svg>
                            <span class="font-medium">Requires Human Review</span>
                        </div>
                        <p class="text-sm mt-1">This content requires manual review due to potential policy violations.</p>
                    </div>
                @endif

                @if($moderation->ai_suggestions)
                    <h3 class="font-medium text-sm mb-2">AI Suggestions</h3>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($moderation->ai_suggestions as $suggestion)
                            <li class="text-sm">{{ $suggestion }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500">No AI suggestions available</p>
                @endif
            </div>
            
            <h2 class="text-lg font-semibold mb-4">Moderation Actions</h2>
            <div class="flex flex-col sm:flex-row gap-4">
                <form method="POST" action="{{ route('moderation.approve', $moderation) }}">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Approve Content
                    </button>
                </form>
                
                <form method="POST" action="{{ route('moderation.reject', $moderation) }}" class="flex-1">
                    @csrf
                    <div class="flex flex-col gap-2">
                        <textarea name="reason" required placeholder="Reason for rejection..."
                            class="w-full border rounded p-2" rows="3"></textarea>
                        <div class="flex gap-2">
                            <button type="button" onclick="applySuggestion('Inappropriate language')"
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">
                                Inappropriate
                            </button>
                            <button type="button" onclick="applySuggestion('Factually incorrect')"
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">
                                Incorrect
                            </button>
                            <button type="button" onclick="applySuggestion('Off-topic content')"
                                class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded">
                                Off-topic
                            </button>
                        </div>
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
                            Reject Content
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @else
        <div class="border-t pt-6">
            <h2 class="text-lg font-semibold mb-2">Moderation Result</h2>
            <div class="flex items-center gap-2 mb-2">
                <span class="font-medium">Status:</span>
                <span class="px-2 py-1 rounded-full text-xs font-bold
                    {{ $moderation->status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ ucfirst($moderation->status) }}
                </span>
            </div>
            @if($moderation->status === 'rejected')
            <div class="mt-2 p-3 bg-red-50 rounded">
                <p class="font-medium">Rejection Reason:</p>
                <p class="mt-1">{{ $moderation->rejection_reason }}</p>
            </div>
            @endif
            
            @if($moderation->moderation_result)
            <div class="mt-4 p-3 bg-blue-50 rounded">
                <p class="font-medium">Moderation Analysis:</p>
                <pre class="mt-1 text-xs">{{ json_encode($moderation->moderation_result, JSON_PRETTY_PRINT) }}</pre>
            </div>
            @endif
            <p><span class="font-medium">Moderated By:</span> {{ $moderation->moderator->name ?? 'System' }}</p>
            <p><span class="font-medium">Moderated At:</span> {{ $moderation->moderated_at->format('M d, Y H:i') }}</p>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    function applySuggestion(suggestion) {
        const textarea = document.querySelector('textarea[name="reason"]');
        if (textarea.value) {
            textarea.value += '\n' + suggestion;
        } else {
            textarea.value = suggestion;
        }
        textarea.focus();
    }
</script>
@endpush
@endsection
