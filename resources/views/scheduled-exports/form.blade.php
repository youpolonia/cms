@props(['export' => null])

<div class="mb-3">
    <label for="name" class="form-label">Export Name</label>
    <input type="text" class="form-control" id="name" name="name" 
           value="{{ old('name', $export?->name) }}" required>
</div>

<div class="mb-3">
    <label for="frequency" class="form-label">Frequency</label>
    <select class="form-select" id="frequency" name="frequency" required>
        <option value="daily" {{ old('frequency', $export?->frequency) === 'daily' ? 'selected' : '' }}>Daily</option>
        <option value="weekly" {{ old('frequency', $export?->frequency) === 'weekly' ? 'selected' : '' }}>Weekly</option>
        <option value="monthly" {{ old('frequency', $export?->frequency) === 'monthly' ? 'selected' : '' }}>Monthly</option>
    </select>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="start_date" class="form-label">Start Date</label>
        <input type="datetime-local" class="form-control" id="start_date" name="start_date"
               value="{{ old('start_date', $export?->start_date?->format('Y-m-d\TH:i')) }}" required>
    </div>
    <div class="col-md-6">
        <label for="end_date" class="form-label">End Date (Optional)</label>
        <input type="datetime-local" class="form-control" id="end_date" name="end_date"
               value="{{ old('end_date', $export?->end_date?->format('Y-m-d\TH:i')) }}">
    </div>
</div>

<div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="anonymize" name="anonymize"
           {{ old('anonymize', $export?->anonymize) ? 'checked' : '' }}>
    <label class="form-check-label" for="anonymize">Anonymize Data</label>
</div>

<div class="mb-3">
    <label class="form-label">Export Parameters</label>
    <div class="border p-3">
        <!-- Export parameters configuration would go here -->
        <p class="text-muted">Configure what data to include in the export</p>
    </div>
</div>