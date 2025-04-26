@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Versions for: {{ $content->title }}</h1>
    
    <div class="card">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>Version #</th>
                        <th>Created At</th>
                        <th>Changed By</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($versions as $version)
                    <tr>
                        <td>{{ $version->version_number }}</td>
                        <td>{{ $version->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $version->user->name }}</td>
                        <td>
                            <span class="badge bg-{{ $version->approval_status === 'approved' ? 'success' : ($version->approval_status === 'rejected' ? 'danger' : 'warning') }}">
                                {{ ucfirst($version->approval_status ?? 'pending') }}
                            </span>
                        </td>
                        <td>{{ $version->change_description }}</td>
                        <td class="d-flex gap-2">
                            <div class="btn-group">
                                <a href="{{ route('content.versions.show', [$content, $version]) }}"
                                   class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('content.versions.restore', [$content, $version]) }}"
                                   class="btn btn-sm btn-warning"
                                   onclick="return confirm('Restore this version?')">Restore</a>
                            </div>
                           
                           @can('approve', $version)
                               @if(!$version->isApproved())
                                   <form method="POST" action="{{ route('content.versions.approve', [$content, $version]) }}" class="d-inline">
                                       @csrf
                                       <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                   </form>
                               @endif
                               
                               @if(!$version->isRejected())
                                   <button type="button"
                                           class="btn btn-sm btn-danger"
                                           data-bs-toggle="modal"
                                           data-bs-target="#rejectModal{{ $version->id }}">
                                       Reject
                                   </button>
                                   
                                   <!-- Rejection Modal -->
                                   <div class="modal fade" id="rejectModal{{ $version->id }}" tabindex="-1">
                                       <div class="modal-dialog">
                                           <div class="modal-content">
                                               <div class="modal-header">
                                                   <h5 class="modal-title">Reject Version #{{ $version->version_number }}</h5>
                                                   <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                               </div>
                                               <form method="POST" action="{{ route('content.versions.reject', [$content, $version]) }}">
                                                   @csrf
                                                   <div class="modal-body">
                                                       <div class="mb-3">
                                                           <label class="form-label">Reason for rejection</label>
                                                           <textarea name="reason" class="form-control" required></textarea>
                                                       </div>
                                                   </div>
                                                   <div class="modal-footer">
                                                       <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                       <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                                                   </div>
                                               </form>
                                           </div>
                                       </div>
                                   </div>
                               @endif
                           @endcan
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            
            {{ $versions->links() }}
        </div>
    </div>
</div>
@endsection