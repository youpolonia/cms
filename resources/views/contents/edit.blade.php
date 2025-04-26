@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Edit Content</h1>
        <a href="{{ route('contents.index') }}" class="btn btn-secondary">
            Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('contents.update', $content) }}" method="POST">
                @csrf
                @method('PUT')
                @include('contents._form')
                <button type="submit" class="btn btn-primary">Update Content</button>
            </form>
        </div>
    </div>
</div>
@endsection