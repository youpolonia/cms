@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-bell mr-2"></i>Test Notification System
                    </h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('scheduling.notifications.test.send') }}">
                        @csrf

                        <div class="form-group row">
                            <label for="type" class="col-md-4 col-form-label text-md-right">
                                Notification Type
                            </label>

                            <div class="col-md-6">
                                <select id="type" class="form-control" name="type" required>
                                    <option value="upcoming">Upcoming Schedule</option>
                                    <option value="conflict">Schedule Conflict</option>
                                    <option value="completed">Publication Completed</option>
                                    <option value="changed">Schedule Changed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-md-4 col-form-label text-md-right">
                                Delivery Channel
                            </label>

                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="channel" 
                                        id="channel_email" value="email" checked>
                                    <label class="form-check-label" for="channel_email">
                                        Email Only
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="channel" 
                                        id="channel_in_app" value="in_app">
                                    <label class="form-check-label" for="channel_in_app">
                                        In-App Only
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="channel" 
                                        id="channel_both" value="both">
                                    <label class="form-check-label" for="channel_both">
                                        Both Channels
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane mr-1"></i> Send Test Notification
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection