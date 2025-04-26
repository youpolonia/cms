<div class="mt-6 bg-gray-50 p-4 rounded-lg">
    <h3 class="text-lg font-medium mb-4">Content Scheduling</h3>
    
    @if($content->is_scheduled)
        <div class="mb-4">
            <p class="text-sm text-gray-600">
                Scheduled to publish on: {{ $content->publish_at->format('Y-m-d H:i') }}
                @if($content->expire_at)
                    <br>Expires on: {{ $content->expire_at->format('Y-m-d H:i') }}
                @endif
            </p>
            <form method="POST" action="{{ route('contents.unschedule', $content) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger mt-2">
                    Cancel Schedule
                </button>
            </form>
        </div>
    @else
        <form method="POST" action="{{ route('contents.schedule', $content) }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="publish_at" class="block text-sm font-medium text-gray-700">
                        Publish Date/Time
                    </label>
                    <input type="datetime-local" id="publish_at" name="publish_at" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" 
                           min="{{ now()->format('Y-m-d\TH:i') }}" required>
                </div>
                
                <div>
                    <label for="expire_at" class="block text-sm font-medium text-gray-700">
                        Expire Date/Time (optional)
                    </label>
                    <input type="datetime-local" id="expire_at" name="expire_at" 
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" 
                           min="{{ now()->addHour()->format('Y-m-d\TH:i') }}">
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    Schedule Content
                </button>
            </div>
        </form>
    @endif
</div>