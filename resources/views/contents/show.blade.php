@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>{{ $content->title }}</h1>
        <div>
            <a href="{{ route('contents.edit', $content) }}" class="btn btn-primary">Edit</a>
            <a href="{{ route('contents.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="mb-3">
                <h5>Content Type</h5>
                <p>{{ ucfirst($content->content_type) }}</p>
            </div>
            <div class="mb-3">
                <h5>Content</h5>
                <div class="border p-3 rounded bg-light">
                    {!! $content->content !!}
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">SEO Information</div>
        <div class="card-body">
            <div class="mb-3">
                <h5>SEO Title</h5>
                <p>{{ $content->seo_title ?? 'Not set' }}</p>
            </div>
            <div class="mb-3">
                <h5>SEO Description</h5>
                <p>{{ $content->seo_description ?? 'Not set' }}</p>
            </div>
            <div class="mb-3">
                <h5>SEO Keywords</h5>
                <p>{{ $content->seo_keywords ? implode(', ', $content->seo_keywords) : 'Not set' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection