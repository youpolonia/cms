@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Recycle Bin</h1>
        @if($trashedContents->isNotEmpty())
            <form action="{{ route('content.recycle-bin.empty') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger" onclick="return confirm('Permanently delete ALL trashed content?')">
                    Empty Recycle Bin
                </button>
            </form>
        @endif
    </div>

    @if($trashedContents->isEmpty())
        <div class="alert alert-info">
            The recycle bin is empty
        </div>
    @else
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Deleted By</th>
                                <th>Deleted At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trashedContents as $content)
                            <tr>
                                <td>{{ $content->title }}</td>
                                <td>{{ $content->user->name ?? 'System' }}</td>
                                <td>{{ $content->deleted_at->format('Y-m-d H:i') }}</td>
                                <td class="d-flex gap-2">
                                    <form action="{{ route('content.recycle-bin.restore', $content->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Restore</button>
                                    </form>
                                    <form action="{{ route('content.recycle-bin.force-delete', $content->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Permanently delete this content?')">
                                            Delete Permanently
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $trashedContents->links() }}
        </div>
    @endif
</div>
@endsection