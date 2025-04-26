@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Schedule Content: {{ $content->title }}</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('contents.schedule.store', $content) }}">
                @csrf

                <div class="mb-3">
                    <label for="publish_at" class="form-label">Publish Date/Time</label>
                    <input type="datetime-local" class="form-control" id="publish_at" 
                           name="publish_at" required min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <div class="mb-3">
                    <label for="unpublish_at" class="form-label">Unpublish Date/Time (Optional)</label>
                    <input type="datetime-local" class="form-control" id="unpublish_at" 
                           name="unpublish_at" min="{{ now()->format('Y-m-d\TH:i') }}">
                </div>

                <button type="submit" class="btn btn-primary">Schedule Content</button>
                <a href="{{ route('content.show', $content) }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
@endsection