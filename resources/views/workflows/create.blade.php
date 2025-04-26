@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Create New Workflow</div>

                <div class="card-body">
                    <form method="POST" action="{{ route('workflows.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Workflow Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="error_category_id" class="form-label">Error Category</label>
                            <select class="form-select" id="error_category_id" name="error_category_id" required>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Create Workflow</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection