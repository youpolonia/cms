@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Versions for "{{ $content->title }}"</h1>
        <div class="flex space-x-2">
            <a href="{{ route('content.show', $content) }}" 
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Back to Content
            </a>
            @can('create', [App\Models\ContentBranch::class, $content])
            <a href="#" 
               class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded"
               onclick="document.getElementById('create-branch-form').classList.toggle('hidden')">
                Create New Branch
            </a>
            @endcan
        </div>
    </div>

    @can('create', [App\Models\ContentBranch::class, $content])
    <div id="create-branch-form" class="hidden bg-white rounded-lg shadow-md p-6 mb-6">
        <form method="POST" action="{{ route('content.versions.create-branch', $content) }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Branch Name</label>
                    <input type="text" name="name" id="name" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <input type="text" name="description" id="description"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>
            </div>
            <div class="mt-4">
                <button type="submit" 
                        class="inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Create Branch
                </button>
            </div>
        </form>
    </div>
    @endcan

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">Version History</h2>
                <div class="flex space-x-2">
                    <div class="relative">
                        <select id="branch-filter" 
                                class="appearance-none bg-white border border-gray-300 rounded-md pl-3 pr-8 py-2 text-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Branches</option>
                            @foreach($content->branches as $branch)
                            <option value="{{ $branch->id }}" @if($branch->is_default) selected @endif>
                                {{ $branch->name }}
                                @if($branch->is_default) (Default) @endif
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="divide-y divide-gray-200">
            @foreach($versions as $version)
            <div class="px-6 py-4 hover:bg-gray-50" data-branch="{{ $version->branch_id }}">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center justify-center h-10 w-10 rounded-full bg-blue-500 text-white font-bold">
                                v{{ $version->version_number }}
                            </span>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-900">
                                {{ $version->title }}
                                @if($version->is_current)
                                <span class="ml-2 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    Current
                                </span>
                                @endif
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ $version->user->name }} · {{ $version->created_at->diffForHumans() }}
                                · Branch: {{ $version->branch->name }}
                            </p>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('content.versions.show', [$content, $version]) }}" 
                           class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            View
                        </a>
                        @if(!$version->is_current)
                        <a href="{{ route('content.versions.compare', [$content, $content->currentVersion, $version]) }}" 
                           class="inline-flex items-center px-3 py-1 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Compare
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="px-6 py-4 border-t border-gray-200">
            {{ $versions->links() }}
        </div>
    </div>

    @livewire('content-version-comparison', ['contentId' => $content->id])
</div>

<script>
document.getElementById('branch-filter').addEventListener('change', function() {
    const branchId = this.value;
    const items = document.querySelectorAll('[data-branch]');
    
    items.forEach(item => {
        if (!branchId || item.dataset.branch === branchId) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});
</script>
@endsection
