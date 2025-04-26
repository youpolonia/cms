<div class="checkbox-container">
    <input 
        type="checkbox" 
        id="{{ $name }}" 
        name="{{ $name }}" 
        value="{{ $value }}"
        {{ $checked ? 'checked' : '' }}
        class="checkbox-input"
    >
    @if($label)
        <label for="{{ $name }}" class="checkbox-label">
            {{ $label }}
        </label>
    @endif
</div>

<style>
.checkbox-container {
    display: flex;
    align-items: center;
    margin: 0.5rem 0;
}
.checkbox-input {
    width: 1rem;
    height: 1rem;
    margin-right: 0.5rem;
}
.checkbox-label {
    font-size: 0.875rem;
    color: #374151;
}
</style>