@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h2>Create Approval Workflow for: {{ $content->title }}</h2>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('approval-workflows.store', $content) }}">
                        @csrf

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">
                                Workflow Name
                            </label>

                            <div class="col-md-6">
                                <input id="name" type="text" 
                                    class="form-control @error('name') is-invalid @enderror" 
                                    name="name" 
                                    value="{{ old('name') }}" 
                                    required autofocus>

                                @error('name')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                        </div>

                        <div id="steps-container">
                            <!-- Steps will be added here dynamically -->
                        </div>

                        <div class="form-group row mb-4">
                            <div class="col-md-8 offset-md-4">
                                <button type="button" id="add-step" class="btn btn-secondary">
                                    Add Approval Step
                                </button>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary">
                                    Create Workflow
                                </button>
                                <a href="{{ route('contents.show', $content) }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stepsContainer = document.getElementById('steps-container');
    const addStepBtn = document.getElementById('add-step');
    let stepCount = 0;

    addStepBtn.addEventListener('click', function() {
        stepCount++;
        const stepDiv = document.createElement('div');
        stepDiv.className = 'card mb-3 step-card';
        stepDiv.innerHTML = `
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Step #${stepCount}</h5>
                <button type="button" class="btn btn-sm btn-danger remove-step">Remove</button>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label>Step Name</label>
                    <input type="text" name="steps[${stepCount}][name]" 
                        class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Approvers</label>
                    <select name="steps[${stepCount}][approvers][]" 
                        class="form-control select2" multiple required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        `;
        stepsContainer.appendChild(stepDiv);

        // Initialize select2 for the new select element
        $(stepDiv).find('.select2').select2();
    });

    // Handle step removal
    stepsContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-step')) {
            e.target.closest('.step-card').remove();
            stepCount--;
        }
    });
});
</script>

<style>
.step-card {
    border: 1px solid rgba(0,0,0,.125);
    border-radius: .25rem;
}
.select2-container {
    width: 100% !important;
}
</style>
@endsection