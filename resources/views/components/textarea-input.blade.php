<div class="textarea-container">
    @if($label)
        <label for="{{ $name }}" class="textarea-label">
            {{ $label }}
        </label>
    @endif
    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        class="textarea-input"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
    >{{ $value }}</textarea>
</div>

<style>
.textarea-container {
    margin: 0.5rem 0;
}
.textarea-label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    color: #374151;
}
.textarea-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    background-color: white;
    resize: vertical;
}
</style>