@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Schedule New Export</h1>
        <a href="{{ route('scheduled-exports.index') }}" class="btn btn-outline-secondary">
            Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('scheduled-exports.store') }}" method="POST">
                @csrf
                @include('scheduled-exports.form')
                <button type="submit" class="btn btn-primary">Schedule Export</button>
            </form>
        </div>
    </div>
</div>
@endsection