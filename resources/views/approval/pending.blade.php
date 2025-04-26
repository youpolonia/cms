@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">Pending Approvals</div>

                    <div class="card-body">
                        @if($contents->isEmpty())
                            <div class="alert alert-info">
                                No content pending your approval.
                            </div>
                        @else
                            @foreach($contents as $content)
                                @if($loop->first)
                                    <x-approval-timeline 
                                        :approvals="$content->approvals"
                                        :currentStep="$content->approvalWorkflow->currentStep->order"
                                        :totalSteps="$content->approvalWorkflow->steps->count()"
                                        :progress="[
                                            'completed' => $content->approvals->where('status', '!=', 'pending')->count(),
                                            'total' => $content->approvalWorkflow->steps->count(),
                                            'percentage' => ($content->approvals->where('status', '!=', 'pending')->count() / $content->approvalWorkflow->steps->count()) * 100
                                        ]"
                                        :timeMetrics="$content->approvalWorkflow->time_metrics"
                                        :notificationSettings="$content->approvalWorkflow->notification_settings"
                                    />
                                @endif
                            @endforeach
                            
                            <div class="table-responsive mt-6">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Submitted</th>
                                            <th>Current Step</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($contents as $content)
                                            <tr>
                                                <td>{{ $content->title }}</td>
                                                <td>{{ $content->user->name }}</td>
                                                <td>{{ $content->created_at->diffForHumans() }}</td>
                                                <td>
                                                    {{ $content->approvalWorkflow->currentStep->name }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('content.versions.compare', [
                                                        'content' => $content,
                                                        'version1' => $content->versions->first()->id,
                                                        'version2' => $content->versions->latest()->first()->id
                                                    ]) }}" 
                                                       class="btn btn-sm btn-info"
                                                       target="_blank">
                                                        Review Changes
                                                    </a>
                                                    <form action="{{ route('approval.approve', $content) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            Approve
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-sm btn-danger" 
                                                            data-toggle="modal" 
                                                            data-target="#rejectModal{{ $content->id }}">
                                                        Reject
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @foreach($contents as $content)
        <!-- Rejection Modal -->
        <div class="modal fade" id="rejectModal{{ $content->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Content</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <form action="{{ route('approval.reject', $content) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="reason">Reason for rejection</label>
                                <textarea class="form-control" id="reason" name="reason" required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Submit Rejection</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
