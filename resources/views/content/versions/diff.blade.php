@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Compare Versions #{{ $version1->id }} and #{{ $version2->id }}</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            Version Details
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Version #{{ $version1->id }}</h5>
                    <p><strong>Created:</strong> {{ $version1->created_at->format('Y-m-d H:i') }}</p>
                    <p><strong>By:</strong> {{ $version1->user->name }}</p>
                </div>
                <div class="col-md-6">
                    <h5>Version #{{ $version2->id }}</h5>
                    <p><strong>Created:</strong> {{ $version2->created_at->format('Y-m-d H:i') }}</p>
                    <p><strong>By:</strong> {{ $version2->user->name }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            Comparison Statistics
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h5>Content Changes</h5>
                    <p>Total Changes: {{ $stats->change_count }}</p>
                    <p>Additions: <span class="text-success">{{ $stats->additions }}</span></p>
                    <p>Deletions: <span class="text-danger">{{ $stats->deletions }}</span></p>
                </div>
                <div class="col-md-4">
                    <h5>View Counts</h5>
                    <p>Version 1 Views: {{ $stats->version1_views }}</p>
                    <p>Version 2 Views: {{ $stats->version2_views }}</p>
                </div>
                <div class="col-md-4">
                    <h5>Comparison</h5>
                    <p>Similarity: {{ $stats->similarity }}%</p>
                    <p>Changed Lines: {{ $stats->changed_lines }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Content Differences
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Version #{{ $version1->id }}</h5>
                    <div class="border p-3 bg-light">
                        @foreach($diff['old'] as $line)
                            @if($line['type'] === 'removed')
                                <div class="text-danger">{{ $line['text'] }}</div>
                            @else
                                <div>{{ $line['text'] }}</div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <div class="col-md-6">
                    <h5>Version #{{ $version2->id }}</h5>
                    <div class="border p-3 bg-light">
                        @foreach($diff['new'] as $line)
                            @if($line['type'] === 'added')
                                <div class="text-success">{{ $line['text'] }}</div>
                            @else
                                <div>{{ $line['text'] }}</div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection