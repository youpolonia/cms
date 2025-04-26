<div class="bg-white rounded-lg shadow overflow-hidden">
    @if(str_starts_with($item->media->metadata['mime_type'], 'image/'))
        <img src="{{ Storage::url($item->media->path) }}" 
            alt="{{ $item->media->filename }}" 
            class="w-full h-48 object-cover">
    @else
        <div class="bg-gray-100 w-full h-48 flex items-center justify-center">
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
            </svg>
        </div>
    @endif

    <div class="p-4">
        <div class="flex justify-between items-start">
            <h3 class="font-medium truncate">{{ $item->media->filename }}</h3>
            <form action="{{ route('media.collections.update-positions', $item->pivot->collection_id) }}" method="POST">
                @csrf
                <input type="hidden" name="positions[0][media_id]" value="{{ $item->media->id }}">
                <input type="hidden" name="positions[0][position]" value="{{ $item->pivot->position }}">
                <input type="hidden" name="positions[0][caption]" value="{{ $item->pivot->caption }}">
                <input type="hidden" name="positions[0][is_featured]" value="{{ $item->pivot->is_featured ? 0 : 1 }}">
                <button type="submit" class="text-yellow-500 hover:text-yellow-600">
                    @if($item->pivot->is_featured)
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                        </svg>
                    @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                        </svg>
                    @endif
                </button>
            </form>
        </div>
        <div class="mt-2 flex justify-between items-center">
            <a href="{{ route('media.show', $item->media) }}" 
                class="text-sm text-blue-600 hover:underline">
                View Details
            </a>
            <form action="{{ route('media.collections.remove', [$item->pivot->collection_id, $item->media]) }}" 
                method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-sm text-red-600 hover:underline">
                    Remove
                </button>
            </form>
        </div>
    </div>
</div>
