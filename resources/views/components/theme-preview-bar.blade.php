@php
    $themeService = app(\App\Services\ThemeService::class);
@endphp

@if($themeService->isPreviewing())
<div class="fixed bottom-0 left-0 right-0 bg-yellow-500 text-black p-2 z-50">
    <div class="container mx-auto flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <div class="flex items-center">
                Previewing: <strong class="ml-1">{{ $themeService->getPreviewTheme() }}</strong>
                @if($themeService->hasMarketplaceUpdate())
                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-xs px-2 py-0.5 rounded-full">
                        Update Available
                    </span>
                @endif
            </div>
            <div class="text-sm">
                Original: <strong>{{ $themeService->getOriginalTheme() }}</strong>
            </div>
            <div class="text-sm">
                Time left: <strong>{{ $themeService->getPreviewTimeLeft() }} minutes</strong>
            </div>
        </div>
        <div>
            <form action="{{ route('themes.reset-preview') }}" method="POST">
                @csrf
                <button type="submit" class="bg-black text-white px-3 py-1 rounded hover:bg-gray-800">
                    Exit Preview
                </button>
            </form>
        </div>
    </div>
</div>
@endif
