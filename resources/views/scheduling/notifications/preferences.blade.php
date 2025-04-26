@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Notification Preferences</h1>
            <p class="text-muted">Customize how you receive scheduling notifications</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="mb-0">Notification Settings</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('scheduling.notifications.preferences.update') }}">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <h5 class="mb-3 border-bottom pb-2">
                                <i class="fas fa-envelope mr-2"></i>Email Notifications
                            </h5>
                            
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="email_upcoming" 
                                    name="email_upcoming" {{ $preferences->email_upcoming ? 'checked' : '' }}>
                                <label class="custom-control-label" for="email_upcoming">
                                    Upcoming schedule notifications
                                </label>
                                <small class="form-text text-muted">
                                    Receive emails when content is about to be published
                                </small>
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="email_conflicts" 
                                    name="email_conflicts" {{ $preferences->email_conflicts ? 'checked' : '' }}>
                                <label class="custom-control-label" for="email_conflicts">
                                    Schedule conflict alerts
                                </label>
                                <small class="form-text text-muted">
                                    Receive emails when scheduling conflicts are detected
                                </small>
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="email_completed" 
                                    name="email_completed" {{ $preferences->email_completed ? 'checked' : '' }}>
                                <label class="custom-control-label" for="email_completed">
                                    Publication confirmations
                                </label>
                                <small class="form-text text-muted">
                                    Receive emails when content has been successfully published
                                </small>
                            </div>

                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="email_changes" 
                                    name="email_changes" {{ $preferences->email_changes ? 'checked' : '' }}>
                                <label class="custom-control-label" for="email_changes">
                                    Schedule change notifications
                                </label>
                                <small class="form-text text-muted">
                                    Receive emails when scheduled content is modified
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <h5 class="mb-3 border-bottom pb-2">
                                <i class="fas fa-bell mr-2"></i>In-App Notifications
                            </h5>
                            
                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="in_app_upcoming" 
                                    name="in_app_upcoming" {{ $preferences->in_app_upcoming ? 'checked' : '' }}>
                                <label class="custom-control-label" for="in_app_upcoming">
                                    Upcoming schedule notifications
                                </label>
                                <small class="form-text text-muted">
                                    Show notifications when content is about to be published
                                </small>
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="in_app_conflicts" 
                                    name="in_app_conflicts" {{ $preferences->in_app_conflicts ? 'checked' : '' }}>
                                <label class="custom-control-label" for="in_app_conflicts">
                                    Schedule conflict alerts
                                </label>
                                <small class="form-text text-muted">
                                    Show notifications when scheduling conflicts are detected
                                </small>
                            </div>

                            <div class="custom-control custom-switch mb-3">
                                <input type="checkbox" class="custom-control-input" id="in_app_completed" 
                                    name="in_app_completed" {{ $preferences->in_app_completed ? 'checked' : '' }}>
                                <label class="custom-control-label" for="in_app_completed">
                                    Publication confirmations
                                </label>
                                <small class="form-text text-muted">
                                    Show notifications when content has been successfully published
                                </small>
                            </div>

                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="in_app_changes" 
                                    name="in_app_changes" {{ $preferences->in_app_changes ? 'checked' : '' }}>
                                <label class="custom-control-label" for="in_app_changes">
                                    Schedule change notifications
                                </label>
                                <small class="form-text text-muted">
                                    Show notifications when scheduled content is modified
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('scheduling.notifications.index') }}" class="btn btn-outline-secondary mr-2">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save Preferences
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection