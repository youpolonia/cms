<div class="select-container">
    @if($label)
        <label for="{{ $name }}" class="select-label">
            {{ $label }}
        </label>
    @endif
    <select 
        id="{{ $name }}" 
        name="{{ $name }}" 
        class="select-input"
    >
        <option value="" disabled selected>{{ $placeholder }}</option>
        @foreach($options as $value => $text)
            <option 
                value="{{ $value }}" 
                {{ $selected == $value ? 'selected' : '' }}
            >
                {{ $text }}
            </option>
        @endforeach
    </select>
</div>

<style>
.select-container {
    margin: 0.5rem 0;
}
.select-label {
    display: block;
    margin-bottom: 0.25rem;
    font-size: 0.875rem;
    color: #374151;
}
.select-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.25rem;
    background-color: white;
}
</style>