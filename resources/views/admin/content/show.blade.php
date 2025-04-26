@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4>{{ $content->title }}</h4>
                        <div>
                            <a href="{{ route('admin.content.edit', $content->id) }}" 
                               class="btn btn-sm btn-outline-primary">Edit</a>
                            <form action="{{ route('admin.content.destroy', $content->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <div class="mb-4">
                        <h5>Content Details</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Type:</strong> {{ ucfirst($content->content_type) }}</p>
                                <p><strong>Author:</strong> {{ $content->user->name }}</p>
                                <p><strong>Created:</strong> {{ $content->created_at->format('M d, Y H:i') }}</p>
                                <p><strong>Updated:</strong> {{ $content->updated_at->format('M d, Y H:i') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Categories:</strong>
                                    @foreach($content->categories as $category)
                                        <span class="badge bg-primary">{{ $category->name }}</span>
                                    @endforeach
                                </p>
                                <p><strong>Slug:</strong> {{ $content->slug }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h5>Content</h5>
                        <div class="border p-3 bg-light">
                            {!! $content->content !!}
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">SEO Information</div>
                        <div class="card-body">
                            <p><strong>SEO Title:</strong> {{ $content->seo_title }}</p>
                            <p><strong>SEO Description:</strong> {{ $content->seo_description }}</p>
                            <p><strong>SEO Keywords:</strong> 
                                @if($content->seo_keywords)
                                    {{ implode(', ', $content->seo_keywords) }}
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection