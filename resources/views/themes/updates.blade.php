@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Theme Updates</h1>
        
        <div class="flex space-x-4">
            <button onclick="checkForUpdates()" 
                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Check for Updates
            </button>
        </div>
    </div>

    @if($themes->isEmpty())
        <div class="bg-white rounded-lg shadow p-6 text-center">
            <p class="text-gray-600">All themes are up to date!</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="divide-y divide-gray-200">
                @foreach($themes as $theme)
                <div class="p-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">{{ $theme->name }}</h2>
                            <p class="text-gray-600 mt-1">{{ $theme->description }}</p>
                        </div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                            Update Available
                        </span>
                    </div>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Current Version</h3>
                            <p class="mt-1 text-gray-600">{{ $theme->currentVersion->version }}</p>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Available Updates</h3>
                            <div class="mt-2 space-y-4">
                                @foreach($theme->availableUpdates as $update)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <span class="font-medium">{{ $update->version }}</span>
                                        <a href="{{ route('themes.updates.install', [$theme, $update->version]) }}"
                                            class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700 transition">
                                            Install
                                        </a>
                                    </div>
                                    
                                    @if($update->changelog)
                                    <div class="mt-3">
                                        <h4 class="text-sm font-medium text-gray-900">Changelog</h4>
                                        <div class="mt-1 prose prose-sm max-w-none text-gray-600">
                                            {!! $update->changelog !!}
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<script>
function checkForUpdates() {
    fetch('{{ route("themes.updates.check") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message.includes('Updates available')) {
            window.location.reload();
        } else {
            alert(data.message);
        }
    });
}
</script>
@endsection
