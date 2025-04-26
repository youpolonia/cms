@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Compare Content Versions</h1>
        <a href="{{ route('content.show', $content) }}" class="btn btn-secondary">
            Back to Content
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h3>{{ $content->title }}</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h4>Version #{{ $version1->version_number }}</h4>
                    <p class="text-muted">
                        Saved by {{ $version1->user->name }} on 
                        {{ $version1->created_at->format('Y-m-d H:i') }}
                    </p>
                </div>
                <div class="col-md-6">
                    <h4>Version #{{ $version2->version_number }}</h4>
                    <p class="text-muted">
                        Saved by {{ $version2->user->name }} on 
                        {{ $version2->created_at->format('Y-m-d H:i') }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <ul class="nav nav-tabs card-header-tabs" id="compareTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="content-tab" data-bs-toggle="tab" 
                            data-bs-target="#content" type="button" role="tab">
                        Content
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="metadata-tab" data-bs-toggle="tab" 
                            data-bs-target="#metadata" type="button" role="tab">
                        Metadata
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="compareTabsContent">
                <div class="tab-pane fade show active" id="content" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="border p-3 mb-3" style="min-height: 300px;">
                                {!! $version1->content !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border p-3 mb-3" style="min-height: 300px;">
                                {!! $version2->content !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="metadata" role="tabpanel">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th>Status</th>
                                    <td>{{ ucfirst($version1->status) }}</td>
                                </tr>
                                <tr>
                                    <th>SEO Title</th>
                                    <td>{{ $version1->seo_title }}</td>
                                </tr>
                                <tr>
                                    <th>SEO Description</th>
                                    <td>{{ $version1->seo_description }}</td>
                                </tr>
                                <tr>
                                    <th>Tags</th>
                                    <td>{{ $version1->tags->pluck('name')->join(', ') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table">
                                <tr>
                                    <th>Status</th>
                                    <td>{{ ucfirst($version2->status) }}</td>
                                </tr>
                                <tr>
                                    <th>SEO Title</th>
                                    <td>{{ $version2->seo_title }}</td>
                                </tr>
                                <tr>
                                    <th>SEO Description</th>
                                    <td>{{ $version2->seo_description }}</td>
                                </tr>
                                <tr>
                                    <th>Tags</th>
                                    <td>{{ $version2->tags->pluck('name')->join(', ') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <form method="POST" action="{{ route('content.restore-version', $version1) }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                Restore Version #{{ $version1->version_number }}
            </button>
        </form>
    </div>
</div>
@endsection