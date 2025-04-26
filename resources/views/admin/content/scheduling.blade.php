@extends('layouts.admin')

@section('title', $title)

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold">{{ $title }}</h1>
            <a href="{{ route('contents.edit', $content) }}" class="btn btn-secondary">
                Back to Content
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                @livewire('content-scheduling', ['content' => $content])
            </div>
        </div>
    </div>
@endsection