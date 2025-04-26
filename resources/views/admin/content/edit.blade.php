@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Content: {{ $content->title }}</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('admin.content.update', $content->id) }}">
                        @csrf
                        @method('PUT')
                        <x-admin.content.form 
                            :content="$content"
                            :categories="$categories" 
                            :contentTypes="$contentTypes" />

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                Update Content
                            </button>
                            <a href="{{ route('admin.content.index') }}" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize rich text editor
    ClassicEditor
        .create(document.querySelector('#content'))
        .catch(error => {
            console.error(error);
        });
</script>
@endpush