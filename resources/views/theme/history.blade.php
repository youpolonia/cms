@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold">Version History: {{ $theme->name }}</h1>
        <a href="{{ route('themes.index') }}" class="btn btn-secondary">
            Back to Themes
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="divide-y divide-gray-200">
            @foreach ($versions as $version)
            <div class="p-6 hover:bg-gray-50 transition-colors duration-150">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">
                            Version {{ $version->version_number }}
                        </h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Created by {{ $version->creator->name }} on 
                            {{ $version->created_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        @if(!$loop->first)
                        <a href="{{ route('themes.compare', [
                            'theme' => $theme,
                            'versionA' => $version->id,
                            'versionB' => $versions[$loop->index - 1]->id
                        ]) }}" 
                        class="btn btn-sm btn-outline-primary">
                            Compare with previous
                        </a>
                        @endif
                        <a href="{{ route('themes.stats', [
                            'theme' => $theme,
                            'versionA' => $version->id,
                            'versionB' => $versions->first()->id
                        ]) }}" 
                        class="btn btn-sm btn-outline-secondary">
                            View stats
                        </a>
                    </div>
                </div>
                @if($version->notes)
                <div class="mt-3 text-sm text-gray-700">
                    {{ $version->notes }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6">
        {{ $versions->links() }}
    </div>
</div>
@endsection