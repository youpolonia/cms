@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Edit Tag</h1>

    <div class="bg-white rounded-lg shadow p-6">
        <form action="{{ route('tags.update', $tag) }}" method="POST">
            @csrf
            @method('PUT')
            @include('tags._form')

            <div class="mt-6">
                <button type="submit" class="btn btn-primary">
                    Update Tag
                </button>
                <a href="{{ route('tags.index') }}" class="btn btn-secondary ml-2">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection