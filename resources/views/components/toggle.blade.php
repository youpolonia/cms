<div class="toggle-wrapper">
    <input type="checkbox" 
           id="{{ $name }}" 
           name="{{ $name }}" 
           class="toggle-input"
           @if($checked) checked @endif>
    <label for="{{ $name }}" class="toggle-label"></label>
</div>