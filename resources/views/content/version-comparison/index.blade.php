@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Version Comparison for: {{ $content->title }}</h1>
        <a href="{{ route('contents.show', $content) }}" class="btn btn-secondary">
            Back to Content
        </a>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('content.version-comparison.compare', $content) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="from_version" class="block mb-2 font-medium">From Version</label>
                    <select name="from_version" id="from_version" class="form-select w-full">
                        @foreach($versions as $version)
                            <option value="{{ $version->id }}">
                                Version {{ $version->version_number }} - {{ $version->created_at->format('M d, Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="to_version" class="block mb-2 font-medium">To Version</label>
                    <select name="to_version" id="to_version" class="form-select w-full">
                        @foreach($versions as $version)
                            <option value="{{ $version->id }}" @if($loop->first) selected @endif>
                                Version {{ $version->version_number }} - {{ $version->created_at->format('M d, Y H:i') }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">
                Compare Versions
            </button>
        </form>
    </div>

    @if($versions->count() > 0)
        <div class="mt-8">
            <h2 class="text-xl font-semibold mb-4">Version History</h2>
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Version</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Author</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($versions as $version)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    Version {{ $version->version_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $version->created_at->format('M d, Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ $version->creator->name ?? 'System' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="{{ route('contents.versions.show', [$content, $version]) }}" 
                                       class="text-blue-600 hover:text-blue-900 mr-3">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $versions->links() }}
            </div>
        </div>
    @endif
</div>
@endsection