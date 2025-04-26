@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Scheduled Export</h1>
        <a href="{{ route('scheduled-exports.index') }}" class="btn btn-outline-secondary">
            Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('scheduled-exports.update', $export) }}" method="POST">
                @csrf
                @method('PUT')
                @include('scheduled-exports.form', ['export' => $export])
                <button type="submit" class="btn btn-primary">Update Export</button>
            </form>
        </div>
    </div>
</div>
@endsection