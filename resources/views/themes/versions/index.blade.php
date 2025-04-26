@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Version History: {{ $theme->name }}</h1>
            <div class="flex space-x-2">
                <a href="{{ route('themes.versions.export', $theme) }}" 
                   class="btn btn-primary">
                    Export All Versions
                </a>
                <a href="{{ route('themes.show', $theme) }}" 
                   class="btn btn-secondary">
                    Back to Theme
                </a>
            </div>
        </div>

        <form id="batch-form" method="POST" action="">
            @csrf
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="p-4 border-b flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <select id="batch-action" class="form-select pr-8" disabled>
                                <option value="">Batch Actions</option>
                                <option value="export">Export Selected</option>
                                <option value="delete">Delete Selected</option>
                                <option value="activate">Activate Selected</option>
                                <option value="deactivate">Deactivate Selected</option>
                            </select>
                            <button type="submit" class="absolute right-0 top-0 h-full px-3 flex items-center justify-center text-gray-400 hover:text-gray-600">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    <div class="w-48">
                        <select id="branch-filter" class="form-select">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-48">
                        <select id="status-filter" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="published">Published</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="w-48">
                        <select id="approval-filter" class="form-select">
                            <option value="">All Approval Statuses</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                    <div class="w-48">
                        <select id="tag-filter" class="form-select">
                            <option value="">All Tags</option>
                            @foreach($allTags as $tag)
                                <option value="{{ $tag }}">{{ $tag }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="relative w-64">
                    <input type="text" id="version-search" placeholder="Search versions..." class="form-input pl-8">
                    <div class="absolute left-3 top-3 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>
            </div>

                <div class="divide-y">
                    @foreach($versions as $version)
                        <div class="p-4 version-item flex items-start" 
                         data-branch="{{ $version->branch_id }}"
                         data-status="{{ $version->status }}"
                         data-search="{{ strtolower($version->version . ' ' . $version->description) }}"
                         data-tags="{{ json_encode($version->tags ?? []) }}">
                        <div class="flex items-start w-full">
                            <div class="flex-shrink-0 mt-1 mr-3">
                                <input type="checkbox" name="versions[]" value="{{ $version->id }}" class="version-checkbox h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            </div>
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                                <span class="text-indigo-600 font-bold">{{ $loop->iteration }}</span>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-lg font-medium">
                                            <a href="{{ route('themes.versions.show', [$theme, $version]) }}" 
                                               class="text-indigo-600 hover:text-indigo-800">
                                                Version {{ $version->getSemanticVersion() }}
                                                @if($version->wasRolledBack())
                                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                                        Rolled Back
                                                    </span>
                                                @endif
                                            </a>
                                        </h3>
                                        @if($version->branch)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $version->branch->is_default ? 'green' : 'gray' }}-100 text-{{ $version->branch->is_default ? 'green' : 'gray' }}-800">
                                                {{ $version->branch->name }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <div class="text-sm text-gray-500">
                                            {{ $version->created_at->format('M j, Y H:i') }}
                                        </div>
                                        @if($version->canBeRolledBack())
                                            <a href="{{ route('themes.versions.rollback-confirm', [$theme, $version]) }}" 
                                               class="text-sm text-red-600 hover:text-red-800">
                                                Rollback
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                <div class="mt-1 text-sm text-gray-600">
                                    {{ $version->description }}
                                </div>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $version->isPublished() ? 'blue' : 'yellow' }}-100 text-{{ $version->isPublished() ? 'blue' : 'yellow' }}-800">
                                        {{ $version->status }}
                                    </span>
                                    @if($version->canBeRolledBack())
                                        <a href="{{ route('themes.versions.rollback-confirm', [$theme, $version]) }}" 
                                           class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200">
                                            Rollback
                                        </a>
                                    @endif
                                    <a href="{{ route('themes.versions.rollback-history', [$theme, $version]) }}" 
                                       class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 hover:bg-blue-200">
                                        Rollback History
                                    </a>
                                    @if($version->approval_status && $version->canBeRolledBack())
                                        <div x-data="{ showRollbackConfirm: false }">
                                            <button @click="showRollbackConfirm = true" 
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200">
                                                Rollback
                                            </button>
                                            
                                            <div x-show="showRollbackConfirm" 
                                                 x-transition
                                                 class="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
                                                <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
                                                    <h3 class="text-lg font-medium text-gray-900">Confirm Rollback</h3>
                                                    <div class="mt-2">
                                                        <p class="text-sm text-gray-500">
                                                            Are you sure you want to rollback to this version? This will revert all changes made since this version.
                                                        </p>
                                                    </div>
                                                    <div class="mt-4 flex justify-end space-x-3">
                                                        <button @click="showRollbackConfirm = false" 
                                                                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                                            Cancel
                                                        </button>
                                                        <a href="{{ route('themes.versions.rollback', [$theme, $version]) }}" 
                                                           class="px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                                            Confirm Rollback
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if($version->approval_status)
                                        <div class="space-y-2">
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ 
                                                    $version->approval_status === 'approved' ? 'green' : 
                                                    ($version->approval_status === 'rejected' ? 'red' : 'blue')
                                                }}-100 text-{{ 
                                                    $version->approval_status === 'approved' ? 'green' : 
                                                    ($version->approval_status === 'rejected' ? 'red' : 'blue')
                                                }}-800">
                                                    {{ ucfirst($version->approval_status) }}
                                                </span>
                                                @if($version->current_approval_step)
                                                    <span class="text-xs text-gray-500">
                                                        Step {{ $version->current_approval_step->order }} of {{ $version->approvalWorkflow->steps->count() }}
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            @if($version->approval_progress)
                                                <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                    <div class="bg-blue-600 h-1.5 rounded-full" 
                                                         style="width: {{ $version->approval_progress['percentage'] }}%"></div>
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    {{ $version->approval_progress['completed'] }}/{{ $version->approval_progress['total'] }} steps completed
                                                </div>
                                            @endif

                                            @if($version->approval_status === 'approved' && $version->approver)
                                                <div class="text-xs text-gray-500">
                                                    Approved by {{ $version->approver->name }} on {{ $version->approved_at->format('M j, Y') }}
                                                </div>
                                            @endif

                                            @if($version->approval_status === 'pending' && $version->canBeApprovedBy(auth()->user()))
                                                <div class="flex space-x-2 mt-1">
                                                    <a href="{{ route('themes.versions.approve', [$theme, $version]) }}" 
                                                       class="text-xs px-2 py-1 bg-green-100 text-green-800 rounded hover:bg-green-200">
                                                        Approve
                                                    </a>
                                                    <a href="{{ route('themes.versions.reject', [$theme, $version]) }}" 
                                                       class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded hover:bg-red-200">
                                                        Reject
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    @if($version->tags)
                                        @foreach($version->tags as $tag)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                {{ $tag }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($version->parentVersion)
                            <div class="ml-14 mt-2 text-xs text-gray-500">
                                Based on: 
                                <a href="{{ route('themes.versions.show', [$theme, $version->parentVersion]) }}" 
                                   class="text-indigo-500 hover:text-indigo-700">
                                    Version {{ $version->parentVersion->getSemanticVersion() }}
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const branchFilter = document.getElementById('branch-filter');
    const statusFilter = document.getElementById('status-filter');
    const approvalFilter = document.getElementById('approval-filter');
    const tagFilter = document.getElementById('tag-filter');
    const versionSearch = document.getElementById('version-search');
    const versionItems = document.querySelectorAll('.version-item');

    function updateBatchActionState() {
        const checkboxes = document.querySelectorAll('.version-checkbox:checked');
        const batchAction = document.getElementById('batch-action');
        const batchForm = document.getElementById('batch-form');

        if (checkboxes.length > 0) {
            batchAction.disabled = false;
            
            // Update form action based on selected batch action
            batchForm.action = {
                'export': () => {
                    const selected = Array.from(document.querySelectorAll('input[name="selected[]"]:checked'))
                        .map(el => el.value);
                    
                    fetch('/api/themes/versions/batch-export', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            versions: selected,
                            theme_id: {{ $theme->id }}
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert('Batch export queued! You will be notified when ready.');
                    });
                },
                'delete': "{{ route('themes.versions.batch-delete', $theme) }}",
                'activate': "{{ route('themes.versions.batch-update-status', $theme) }}",
                'deactivate': "{{ route('themes.versions.batch-update-status', $theme) }}"
            }[batchAction.value];

            // Add status parameter for activate/deactivate
            if (batchAction.value === 'activate' || batchAction.value === 'deactivate') {
                const statusInput = document.createElement('input');
                statusInput.type = 'hidden';
                statusInput.name = 'status';
                statusInput.value = batchAction.value === 'activate' ? '1' : '0';
                batchForm.appendChild(statusInput);
            }
        } else {
            batchAction.disabled = true;
        }
    }

    function filterVersions() {
        const branchValue = branchFilter.value;
        const statusValue = statusFilter.value;
        const approvalValue = approvalFilter.value;
        const tagValue = tagFilter.value;
        const searchValue = versionSearch.value.toLowerCase();

        versionItems.forEach(item => {
            const branchMatch = !branchValue || item.dataset.branch === branchValue;
            const statusMatch = !statusValue || item.dataset.status === statusValue;
            const approvalMatch = !approvalValue || 
                (approvalValue === 'pending' && item.querySelector('.bg-gray-100')) ||
                (approvalValue === 'approved' && item.querySelector('.bg-green-100')) ||
                (approvalValue === 'rejected' && item.querySelector('.bg-red-100'));
            const searchMatch = !searchValue || item.dataset.search.includes(searchValue);
            const tagMatch = !tagValue || item.dataset.tags?.includes(tagValue);

            if (branchMatch && statusMatch && approvalMatch && searchMatch && tagMatch) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }

    // Add event listeners
    branchFilter.addEventListener('change', filterVersions);
    statusFilter.addEventListener('change', filterVersions);
    approvalFilter.addEventListener('change', filterVersions);
    tagFilter.addEventListener('change', filterVersions);
    versionSearch.addEventListener('input', filterVersions);

    // Batch action event listeners
    document.querySelectorAll('.version-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBatchActionState);
    });

    document.getElementById('batch-action').addEventListener('change', updateBatchActionState);

    // Select all checkbox
    document.getElementById('select-all').addEventListener('change', function(e) {
        document.querySelectorAll('.version-checkbox').forEach(checkbox => {
            checkbox.checked = e.target.checked;
        });
        updateBatchActionState();
    });
});
</script>
@endpush
