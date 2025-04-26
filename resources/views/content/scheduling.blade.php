@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Content Scheduling</h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Scheduled Publications</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Content</th>
                                <th>Publish At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($scheduled as $content)
                                <tr>
                                    <td>{{ $content->title }}</td>
                                    <td>{{ $content->publish_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <form action="{{ route('content.unschedule', $content) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-danger">Unschedule</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $scheduled->links() }}
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Expiring Content</h3>
                </div>
                <div class="card-body">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Content</th>
                                <th>Expire At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($expiring as $content)
                                <tr>
                                    <td>{{ $content->title }}</td>
                                    <td>{{ $content->expire_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <form action="{{ route('content.unschedule', $content) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-danger">Cancel Expiry</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $expiring->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection