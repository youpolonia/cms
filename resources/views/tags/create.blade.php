@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Create New Tag</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('tags.store') }}" method="POST">
            @csrf
            @include('tags._form')

            <div class="mt-6">
                <button type="submit" class="btn btn-primary">
                    Create Tag
                </button>
                <a href="{{ route('tags.index') }}" class="btn btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection