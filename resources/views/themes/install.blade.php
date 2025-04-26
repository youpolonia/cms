@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Theme Installation Status
                </h1>
                <div class="flex items-center space-x-2">
                    <span class="px-3 py-1 rounded-full text-sm font-medium 
                        {{ $status === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($status) }}
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                <div class="border-b border-gray-200 pb-4">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        @switch($status)
                            @case('success')
                                Installation Complete
                                @break
                            @case('failed')
                                Installation Failed
                                @break
                            @default
                                Installation In Progress
                        @endswitch
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300">
                        {{ $message }}
                    </p>

                    @if($status === 'pending')
                    <div class="mt-4">
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-blue-600 h-2.5 rounded-full animate-pulse" style="width: 45%"></div>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            This may take a few minutes...
                        </p>
                    </div>
                    @endif
                </div>

                @if($status === 'success')
                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200 mb-2">
                        Next Steps
                    </h3>
                    <ul class="list-disc pl-5 space-y-1 text-sm text-green-700 dark:text-green-300">
                        <li>Review the theme in the theme editor</li>
                        <li>Check for any new configuration options</li>
                        <li>Test your site's functionality</li>
                    </ul>
                </div>
                @endif

                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('themes.show', $theme) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        View Theme
                    </a>
                    <a href="{{ route('themes.updates') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        Back to Updates
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
