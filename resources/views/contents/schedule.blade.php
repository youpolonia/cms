@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Schedule Content: {{ $content->title }}</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('contents.schedule', $content) }}">
                        @csrf

                        <div class="form-group row">
                            <label for="publish_at" class="col-md-4 col-form-label text-md-right">
                                Publish Date/Time
                            </label>

                            <div class="col-md-6">
                                <input id="publish_at" type="datetime-local" 
                                    class="form-control @error('publish_at') is-invalid @enderror" 
                                    name="publish_at" 
                                    value="{{ old('publish_at') }}" 
                                    min="{{ now()->format('Y-m-d\TH:i') }}"
                                    required>

                                @error('publish_at')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="expire_at" class="col-md-4 col-form-label text-md-right">
                                Expiration Date/Time (Optional)
                            </label>

                            <div class="col-md-6">
                                <input id="expire_at" type="datetime-local" 
                                    class="form-control @error('expire_at') is-invalid @enderror" 
                                    name="expire_at" 
                                    value="{{ old('expire_at') }}"
                                    min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}">

                                @error('expire_at')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Schedule Content
                                </button>
                                <a href="{{ route('contents.show', $content) }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection