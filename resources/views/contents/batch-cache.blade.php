@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Batch Cache Files</h1>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('contents.batch-cache.process') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="paths">
                    File Paths (one per line)
                </label>
                <textarea 
                    name="paths[]" 
                    id="paths" 
                    rows="10"
                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                    placeholder="Enter file paths to cache (e.g. docs/system-overview.md)"
                    required
                ></textarea>
            </div>

            <div class="flex items-center justify-between">
                <button 
                    type="submit" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                >
                    Cache Files
                </button>
            </div>
        </form>

        @if(session('results'))
            <div class="mt-8">
                <h2 class="text-xl font-semibold mb-4">Cache Results</h2>
                <pre class="bg-gray-100 p-4 rounded">{{ json_encode(session('results'), JSON_PRETTY_PRINT) }}</pre>
            </div>
        @endif
    </div>
@endsection